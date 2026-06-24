# ShopFlow Storefront - Implementation Roadmap

The customer-facing site (`shop/`, Laravel 13 + Inertia + Vue 3 with SSR). The admin app (`admin/`) owns the database schema and writes catalog data; the storefront mostly reads catalog/pricing and writes carts, orders, addresses, and payments. Table definitions come from `docs/ShoFlow db doc.md`. Inventory rules come from `docs/ORDER.md`. Cache keys come from `docs/CACHE.md`.

Legend: `[x]` done, `[~]` partial, `[ ]` not started.

A storefront feature is "done" when it has: read/write Eloquent models for the shared tables, controller(s) returning `Inertia::render(...)` (or JSON resources), Vue page + components, SEO tags, RTL Persian UI, and Pest feature tests.

## Conventions for every page

- SSR on, so the first Persian HTML is indexable. See the SEO section in `AGENTS.md`.
- RTL first, `A Iranian Sans` font, brand color `#ff8615` via the `brand` Tailwind utilities.
- Persian digits and Jalali dates, formatted server-side.
- One Vue component per element; pages under `resources/js/Pages`, shared shells under `resources/js/Layouts`, reusable elements under `resources/js/Components`.
- Catalog tables are read-only here; never recreate or migrate admin-owned tables.

---

## Phase 0 - Foundation

- [x] Inertia + Vue 3 + SSR wired and verified
- [x] RTL Persian root template (`lang="fa" dir="rtl"`)
- [x] `A Iranian Sans` font + brand color `#ff8615`
- [x] Base `AppLayout` + sample `Home` page
- [x] Eloquent models mapping shared tables (read-focused): `Category`, `Brand`, `Product`, `Variety`, `Attribute`, `Image`, `Banner`, `Slider`/`Slide`, `Menu`/`MenuItem`, `Page`, `Faq`, `Review` (+ status enums and `HasOptions` trait). Relations to not-yet-created models (`AttributeGroup`, `Coupon`, `Discount`) are deferred to their phases.
- [ ] Shared UI kit components: `BaseButton`, `PriceTag`, `ProductCard`, `QuantityInput`, `Breadcrumbs`, `Pagination`, `RatingStars`, `EmptyState`
- [ ] Helpers/composables: Persian digits, Jalali date, money formatting (`useFormat`)
- [ ] SEO scaffolding: per-page `<Head>` pattern, shared meta defaults, canonical URL, Open Graph, `robots.txt`
- [ ] Error pages (404 / 500) in Persian, RTL

## Phase 1 - Catalog browsing (highest SEO value, build first)

Read-only catalog. This is where SEO and SSR matter most.

- [ ] Home page: published banners, slider, featured categories, product rows, menu (uses `CACHE.md` keys 1, 2, 8, 3)
- [ ] Category listing page: products by category with filters (brand, attributes, price range), sorting, pagination
- [ ] Product detail page: image gallery, variety selection (price + discount + stock from `varieties`), attributes, related products, breadcrumbs
- [ ] Product reviews (read) on the product page; ratings summary
- [ ] Brand page: products for a brand
- [ ] Search: keyword search over products with results page
- [ ] CMS pages (`pages.{slug}`) and FAQ page (`faqs.{position}`)
- [ ] SEO per page: unique title/description, canonical, Open Graph, JSON-LD `Product` (+ `Offer`), `BreadcrumbList`, `Organization`/`WebSite` on home
- [ ] `sitemap.xml` (categories, products, brands, pages) and correct 200/404 status codes

## Phase 2 - User auth & account

Auth uses the shared `users` table. Password reset via mobile (`mobile_password_resets`) and email (`password_resets`).

- [ ] Register / login (credentials per `users`)
- [ ] Password reset via mobile and via email
- [ ] Account dashboard layout
- [ ] Profile view/edit
- [ ] Addresses: list, create, edit. Editing creates a NEW address inheriting primary status; addresses are never deleted (immutable history, see `AGENTS.md` / `db doc`)
- [ ] Order history + single order view
- [ ] Wishlist (`wishlists`): add/remove, list

## Phase 3 - Cart

Inventory-neutral. A cart never changes `varieties.inventory` (see `ORDER.md`).

- [ ] Cart model mapping `carts` (one row per variety line; user or guest session)
- [ ] Add to cart / update quantity / remove line
- [ ] Cart page + mini-cart component with live totals
- [ ] Merge guest cart into user cart on login
- [ ] Coupon preview at cart (validated, not yet committed)

## Phase 4 - Checkout & payment (commerce core)

- [ ] Checkout: choose address, choose shipping method (`shipping_lines` / `shipping_methods` / `shipping_cities` per-city cost), apply coupon
- [ ] Order creation with `pending` status; line snapshots in `order_varieties`; `order_shippings` for fulfillment
- [ ] Inventory decrement on successful payment only, inside a DB transaction with `SELECT ... FOR UPDATE` row lock on the variety (Strategy A, `ORDER.md`)
- [ ] Manual payment via `receipts` (card-to-card / Paya: tracking code or uploaded receipt image; staff confirm)
- [ ] Online payment via `gateways` + `transactions` (Mellat / Parsian / Zarinpal): redirect + callback that marks the order paid and decrements stock
- [ ] Order confirmation / result page

## Phase 5 - Post-purchase & engagement

- [ ] Order status / tracking view
- [ ] Submit product review (writes `reviews`, moderation status pending)
- [ ] Notifications to the customer (order placed / paid / shipped) - scope TBD

## Phase 6 - Performance & SEO hardening

- [ ] Implement caching per `docs/CACHE.md` (categories tree, banners, sliders, menus, product/category/variety, pages, faqs), invalidated in model `booted()` or observers
- [ ] Image optimization and lazy loading; Persian `alt` text
- [ ] Review Core Web Vitals; tune SSR payload size
- [ ] Structured data and OG coverage audit across all page types

---

## Not in storefront scope

- Admin/Filament management (lives in `admin/`)
- Schema migrations for catalog tables (admin owns them)
- Seller / marketplace features (single-vendor; never add `seller_id`)
- Sub Orders, accounting, guarantees, tickets - admin-side concerns unless a customer-facing view is explicitly requested

---

Keep this roadmap and `AGENTS.md` in sync: when a storefront pattern is introduced or a feature lands, update both.
