# Tags

Status: **built (2026-07-24).** Admin owns the `tags` table + `attribute_tag` pivot + `TagResource`; the storefront renders `/tags/{slug}`. There is no `product_tag` pivot (products are never attached directly).

## What a tag is

A tag is a **SEO landing page for a category and/or attribute filter** — a saved filter turned into its own page with its own slug and content. Category and attributes are **each optional, but at least one must be set**. Three shapes:

- **category + attribute(s)** — e.g. "کفش مردانه قرمز" (shoes + red).
- **category only** — a themed page for a whole category with its own slug/SEO (a near-duplicate of the category page, useful as a campaign landing page).
- **attribute(s) only** — a cross-category page, e.g. "محصولات گیمینگ" (all products carrying the گیمینگ attribute, any category).

A tag is **not**: a free-form product label (no `product_tag` pivot), a dynamic row like best-sellers (that's a sort by `seen`), or a banner/menu (own tables).

## Schema (as built)

| Column | Meaning |
| --- | --- |
| `name` | Tag display name |
| `slug` | Tag URL slug (stable, human-readable Persian), unique |
| `category_id` | FK → `categories`, **nullable**, `cascadeOnDelete` |
| `content` | Editor HTML shown on the tag page (nullable) |
| `title`, `description`, `no_index`, `canonical` | SEO fields (added on build) |
| `created_at` / `updated_at` | timestamps |

Attributes are a **many-to-many** through the `attribute_tag` pivot (`attribute_id` + `tag_id`, unique pair, `cascadeOnDelete`).

**Product resolution:** products in `category_id` (and descendants) — or all categories if no category — that carry the tag's attribute(s). Matching reuses the category page's grouped-attribute logic (`product_attribute` pivot; **OR within an attribute group, AND across groups**). See `AGENTS.md` → Catalog filtering.

## Resolved decisions

- **SEO fields**: schema **extended** with `title`/`description`/`no_index`/`canonical` (matches `categories`/`products`).
- **`type` column**: **dropped** — single-vendor, no sellers.
- **Attributes**: **many** per tag (via `attribute_tag`), not one.
- **Category/attribute optionality**: each optional, **at least one required** (enforced in the admin form via `requiredWithout`, not a DB constraint).

## Implementation

**Admin (`admin/`) — owns the schema:**
- Migrations `create_tags_table` + `create_attribute_tag_table`; `Tag` model (`attributes()` belongsToMany) + factory (`withAttributes()` state) + `TagSeeder`; `TagResource` (category optional, attributes multi-select, `requiredWithout` guard) + lang; `TagResourceTest`.

**Shop (`shop/`) — read/render only:**
- `Tag` read model, `TagDTO`, `app/Actions/Tag/{BuildTagDetail,BuildTagBreadcrumbs}`, thin `TagController@show`, route `GET /tags/{slug}`, `Tags/Show.vue`.
- Reuses `CollectCategoryIds` + `GetCategoryProducts` + `GetCategoryFilters`; the tag's attribute ids are merged into the applied `attributes` filter, and an **empty category list means "no category constraint"** (those two shared actions guard the category `whereIn` behind a non-empty check, so category pages are unaffected).
- SEO: title/description/canonical/no_index from the tag; breadcrumbs Home → [Category …] → Tag (category crumbs omitted for attribute-only tags); JSON-LD `BreadcrumbList`.

## Surfacing tags on the home page

This is how a customer discovers a tag. A tag has `show_on_home` (bool) + `home_order` (int). Admins toggle **Show on Home Page** in `TagResource`, which then shows a wireframe of where it lands. The storefront home renders **one product carousel per featured tag** (`GetTagRows` → `HomeController` `tagRows` prop → the shared `Components/ProductCarousel.vue`), placed after the newest/most-viewed rows and before the brand strip. Each row is titled with the tag name and links to `/tags/{slug}`. The products are resolved with the same category+attribute rules the tag page uses, so a row and the tag's own page never disagree. At most **six** rows render (`GetTagRows::MAX_ROWS`) — each row costs its own queries, so the cap is what keeps the home page's cost flat however many tags staff feature; extras wait their turn behind `home_order`. Only `show_on_home = true` tags appear, ordered by `home_order`; a tag whose filter matches no products is dropped rather than rendered as an empty carousel. (Before 2026-08-07 this was an image strip, `TagStrip.vue` fed by `GetHomeTags` — both removed.)

## Integrating with slides / banners / menus

You can also point a slide's / banner's / menu item's `url` at `/tags/{slug}` to link to a tag. Note: the admin slide/banner URL field currently enforces absolute-URL validation, so relative `/tags/...` paths need that validation relaxed first (not yet done).
