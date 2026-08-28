# ShopFlow Cache Register

Tracks cache keys: what is implemented, and what is identified but not built yet.
When a cache is implemented, move it to the **Implemented** section and record the key, driver, TTL, and where it is invalidated.

Legend: `[ ]` not started, `[x]` implemented.

> **Note:** `variety_counts` on `products` is NOT a cache. It is a denormalized DB column kept in sync by `Variety::booted()` (saved/deleted events calling `syncProductVarietyCount()`). No cache entry needed for it — nothing cached reads it.

---

## The store: one Redis, shared by both apps

**Both apps must use the same cache store, with the same key prefixes.** The storefront *writes* catalog
entries; the admin panel *deletes* them when staff edit a product. That is the whole design, and it is
configuration, not code:

| Setting | Value | Why |
|---|---|---|
| `CACHE_STORE` | `redis` | `file` and `database` are per-app stores — the panel would clear rows the storefront never reads |
| `CACHE_PREFIX` | `shopflow_cache_` | pinned literal, identical in both apps |
| `REDIS_PREFIX` | `shopflow_database_` | Redis applies this *on top of* `CACHE_PREFIX`; both halves must match |

**This was silently wrong before the cache existed.** Both `config/cache.php` files derived the prefix
from `APP_NAME` through `Str::slug`, with different separators — `_cache_` in admin, `-cache-` in the
shop — and `config/database.php` did the same for the Redis prefix. Production was *already* on
`CACHE_STORE=redis` in both apps, so the two were addressing different namespaces: every
`Cache::forget()` in the panel would have succeeded and cleared nothing, with no error anywhere. Both
prefixes are now fixed literals in the config files, pinned in every `.env`/`.env.example`, and held
together by `shop/tests/Feature/CatalogCacheStoreTest.php`.

Redis in production runs `--maxmemory-policy noeviction`. Every entry below has a TTL, so the cache
does not grow without bound, but a new cache with no TTL must not be added there casually.

---

## How keys are built

Everything goes through `App\Support\ProductCache`, which exists as **one file mirrored byte-for-byte
into both apps** (like the enums; enforced by `shop/tests/Feature/ProductCacheMirrorTest.php`, which
compares PHP token streams so the apps' different Pint configs don't fight it).

Keys embed a **generation token**, e.g. `products.{generation}.slug.{slug}`:

- A product's own page can be forgotten precisely — we know its slug.
- A **list** cannot. A category page's payload varies by six filters, a sort and a page number, so the
  key space is unbounded and there is nothing to enumerate. Invalidating every list is therefore one
  write that changes the token; older entries become unreachable and expire on their own TTL.

Detail and list entries carry **separate generations on purpose**. Selling the last unit of a product
changes `varieties.inventory`, which a product page shows and no product card does — so a purchase
clears one product page and leaves every category listing warm. Flushing all listings on every stock
movement would keep them permanently cold on a busy shop.

`ProductCache::flushAll()` is the escape hatch for writes no model event sees: a seeder `truncate()`,
a mass query-builder `update()`, a manual SQL fix.

## Where invalidation lives

Eight observers, registered in **both** apps' `AppServiceProvider` (`invalidateCatalogCache()`),
because both apps write these tables. They split into two groups, and the difference is the important
part.

**Per-product — these `forget` one page (trait: `ForgetsProductCache`).** They fire often, so they have
to be cheap and precise:

| Observer | Fires on | Clears |
|---|---|---|
| `ProductObserver` | product saved / updated / deleted | that product's page; the listings too when a *card* column changed (`heading`, `slug`, `price`, `has_stock`, `status`, `category_id`, `brand_id`, `image_id`) |
| `VarietyObserver` | variety saved / updated / deleted | the parent product's page; the listings only when `price`, `sale_price`, `status` or `product_id` changed |
| `ImageObserver` | image saved / updated / deleted | the owning product's page + the listings (a card falls back to a variety image) |
| `ReviewObserver` | review saved / updated / deleted | the product's page only — no card shows a rating |

**Catalog metadata — these `flush` both generations (trait: `FlushesCatalogCache`).** They cannot be
precise, and they fire rarely enough not to need to be:

| Observer | Fires on | Flushes when |
|---|---|---|
| `CategoryObserver` | category updated / deleted | `heading`, `slug`, `parent_id`, `status` changed |
| `BrandObserver` | brand updated / deleted | `heading`, `slug`, `status` changed |
| `AttributeObserver` | attribute updated / deleted | `value`, `color`, `attribute_group_id` changed |
| `AttributeGroupObserver` | attribute group updated / deleted | `name`, `order` changed |

Why they flush rather than forget: the products showing a renamed **attribute** are reachable only
through three relations at once (`product_attribute` for the spec table, `varieties.attribute_id` for
the primary variant axis, the `attribute_variety` pivot for the secondary ones); a renamed **category**
reaches its whole descendant subtree, because breadcrumbs walk `parent_id` upwards; and renaming an
**attribute group** like "رنگ" touches most of a catalog. Enumerating those sets would mean thousands of
`Cache::forget()` round trips inside one admin save — worse than the single generation bump.

Five things here are load-bearing and easy to get wrong:

1. **`saved` and `updated` are both hooked on the per-product observers.** Neither alone sees every
   write: `increment()`/`decrement()` fire only `updated` (so a paid order's stock decrement would be
   missed), while a pivot-only edit in Filament — `product_attribute` synced after the record itself
   came out clean — fires only `saved`.
2. **The metadata observers hook `updated` only, never `saved`** — deliberately the opposite. `saved`
   also fires for an insert, where `getChanges()` is empty; a "flush on unknown change" rule would then
   flush the catalog once per row of every seeder run. `updated` cannot fire for an insert, and a new
   category is attached to no product, so it correctly changes nothing. (Note `wasRecentlyCreated` is
   *not* a usable guard for this: it stays true for the model instance's whole lifetime, so a
   create-then-edit in one request would skip the flush.)
3. **`products.seen` is ignored.** The storefront bumps the view counter on *every* product-page view.
   Treating it as a content change deletes the entry the same request just wrote, on every visit — the
   cache looks implemented and never serves anything. Covered by a test that fails without the guard.
4. **The old slug is cleared on a rename.** Otherwise the previous URL keeps serving the pre-edit page
   for the rest of its TTL.
5. **A metadata `delete` always flushes, unconditionally.** Deleting metadata rewrites product rows
   *in the database* with no Eloquent event on any product: `products.brand_id`,
   `products.attribute_group_id` and `varieties.attribute_id` are `nullOnDelete`, and
   `product_attribute` / `attribute_variety` `cascadeOnDelete`. `Category` is the exception where this
   is belt-and-braces — `products.category_id` and `categories.parent_id` both `restrictOnDelete`, so
   only a childless, product-less category can go — and it flushes anyway so the rule holds without
   re-auditing six foreign keys.

---

## Implemented

| # | Key | What it caches | Driver | TTL | Invalidated by |
|---|-----|----------------|--------|-----|----------------|
| 5 | `products.{gen}.slug.{slug}` | The whole product page payload: `ProductDTO` (images, gallery, brand, category, specs, reviews, **varieties with price + stock**) plus breadcrumbs and the variety ids. Read in `ProductController@show` | redis | 30 min | `ProductObserver`, `VarietyObserver`, `ImageObserver`, `ReviewObserver` |
| 7 | — | **Subsumed by key 5.** The varieties (with their pivot attributes, price, `sale_price`, `inventory`) are part of the product payload, which is the only place the storefront reads them as a set. A second key would be dead code | — | — | — |
| 6 | `products.{gen}.list.category.{fingerprint}` | Paginated product cards for a category — `GetCategoryProducts`, which also serves the **tag** landing pages. Fingerprint covers category ids, all six filters, sort and page | redis | 15 min | any card-visible product/variety/image write (generation bump) |
| — | `products.{gen}.list.related.{product_id}` | The related-products carousel on a product page. A *list* rather than part of the product payload, because it is cards of **other** products — so any product write refreshes it | redis | 15 min | as above |

### Cached stock is safe, deliberately

The product payload carries variety `inventory`, which a purchase makes wrong. Two things make that
acceptable:

- Every write to a variety clears the entry, so the window is a race, not a TTL.
- **Nothing trusts it when money moves.** `CartController` re-reads the variety, `ValidateCartStock`
  re-checks before opening a payment session, and `DecrementInventoryAndMarkPaid` does a row-locked
  re-check (see `ORDER.md`). Worst case a customer briefly sees a stale quantity cap and is corrected
  server-side. Do not "optimise" those three back into reading the cache.

### Not cached, on purpose

Per-visitor or write paths in `ProductController@show` and the cart/checkout flow: `cartItems`,
`isWishlisted`, `canReview`, and everything in `docs/ORDER.md`'s payment path.

---

## Pending (identified, not implemented)

| # | Cache key / pattern | What it caches | Suggested TTL | Invalidated when |
|---|---------------------|----------------|---------------|-----------------|
| 1 | `categories.tree` | Full nested category tree | 1 hour | Category saved / deleted |
| 2 | `banners.{position}` | Published banners for a given position | 30 min | Banner saved / deleted |
| 8 | `sliders.{position}` | Published slider with its slides for a given position | 30 min | Slider or Slide saved / deleted |
| 3 | `menus.{slug}` | Rendered menu tree for a given slug | 1 hour | Menu or MenuItem saved / deleted |
| 4 | `attributes.group.{group_id}` | Attributes belonging to a group | 1 hour | Attribute saved / deleted |
| 9 | `pages.{slug}` | Single published page record | 1 hour | Page saved / deleted |
| 10 | `faqs.{position}` | FAQs for a given position (null = main FAQ page) | 1 hour | FAQ saved / deleted |
| 11 | `settings.autoload` | Autoloaded site settings (key => content), used for footer/contact | 1 hour | Setting saved / deleted (in admin) |
| 12 | `products.{gen}.list.brand.{fingerprint}` | Brand page product cards (`GetBrandProducts`) — same shape as key 6, so it can reuse `ProductCache::rememberList` | 15 min | already covered by the list generation |
| 13 | `products.{gen}.list.home.{row}` | Home page product rows + tag carousels (`GetProductRows`, `GetTagRows` — the heaviest page in the app) | 15 min | already covered by the list generation |
| 14 | `products.{gen}.list.filters.{fingerprint}` | `GetCategoryFilters`, which runs ~7 queries per category render (facet counts + price bounds) | 15 min | already covered by the list generation |

Keys 12–14 reuse `ProductCache::rememberList`, so the product/variety/image side of their invalidation
is already covered — and as of the metadata observers above, so is the category/brand/attribute side.
Two things still need work when they ship:

- **Key 14 (category filters)** additionally reads `attribute_group_category.as_filter`, which nothing
  invalidates (see "Known staleness").
- **Key 13's tag carousels** need a `TagObserver`. Unlike the category listing — whose key already
  encodes the resolved filter values, so a tag reconfiguration naturally produces a new key — nothing in
  a plain `/` request reflects which tags are currently `show_on_home`, so toggling that flag would
  otherwise never invalidate anything. The newest/most-viewed rows in key 13 need nothing new.

Search results are deliberately left out — the query is free user input, so the key space is unbounded
and hit rates would be poor.

### Known staleness

- ~~A variety's label and colour are stale after an attribute rename.~~ **Fixed** —
  `AttributeObserver::resyncDenormalisedVarieties()` pushes a renamed `value`/`color` onto the
  varieties that copied it, *before* flushing, so the cache rebuilds from corrected rows. It updates
  only rows still holding the pre-change value: those are the ones `Variety::booted()` populated, so a
  row holding anything else was set deliberately and is left alone. That guard is load-bearing for
  `color`, which the panel exposes as a `ColorPicker` on both the variety form and the product form's
  variety repeater — a per-variety swatch differing from its attribute is a real thing staff do.
  Covered by `admin/tests/Feature/AttributeResyncTest.php`. A delete deliberately does *not* resync:
  `varieties.attribute_id` is `nullOnDelete`, and keeping the copied text means the variety still
  renders a readable label instead of going blank.
- **`attribute_group_category.as_filter`** has no invalidation. It is edited as its own entity in the
  panel (`AttributeGroupCategoryResource`), so no `AttributeGroup` event fires, and the metadata
  observers hook `updated` only. It feeds solely the not-yet-built category-filter cache (key 14), so it
  is deferred — but it must be handled when that key ships.
- **A pivot-only edit** that changes no product column relies on Filament firing the parent's `saved`
  event, which it does today. If a future write path syncs `product_attribute` without saving the
  product, add an explicit `ProductCache::forgetProduct()` there.
- **`products.price` / `products.has_stock` are not synced from variety data** by anything. Listings
  filter and sort on those columns while cards display the cheapest *variety* price, so an admin who
  lets them drift makes "cheapest first" disagree with the prices shown. Pre-existing, unrelated to
  caching, and not fixable by it — noted here because it first becomes visible as "the sort looks
  wrong" once listings are cached.

---

## Rules

- Cache keys use dot notation and include any dynamic segment as `{placeholder}`.
- **Go through `App\Support\ProductCache`** for anything product-shaped rather than calling `Cache::`
  directly, so keys and generations stay in one place — and change the file in **both apps** in the
  same commit.
- Invalidation logic goes in an observer (`app/Observers/`) or the model's `booted()` method, never in
  controllers or Filament resources.
- A new cache that a *page* reads must be invalidated by a write in **either** app, so ask which app
  writes the table before deciding where the observer goes.
- Document every new key here before or when implementing it — in both copies of this file.
