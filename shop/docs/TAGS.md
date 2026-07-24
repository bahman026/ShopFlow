# Tags

Status: **planned, not built.** The `tags` table is described in `ShoFlow db doc.md` but has no migration yet in `admin/`. There is no `product_tag` pivot.

## What a tag is

A tag is a **SEO landing page for a `category + attribute` combination** — a saved filter turned into its own page with its own slug and content. Example: "تجهیزات گیمینگ", "لوازم تابستانی", "کفش مردانه قرمز".

A tag is **not**:
- a free-form product label (products are not attached to tags; there is no `product_tag` pivot),
- a dynamic row like best-sellers (that is a sort by `seen`),
- a banner or menu (those have their own tables).

## Schema (as documented)

`tags` columns from `ShoFlow db doc.md`:

| Column | Meaning |
| --- | --- |
| `slug` | Tag URL slug (stable, human-readable Persian) |
| `name` | Tag display name |
| `category_id` | The category the tag scopes to |
| `attribute_id` | The attribute the tag filters by |
| `content` | Editor content shown on the tag page |
| `type` | Tag type (e.g. user/seller in the source schema) |
| `created_at` | Creation date |

A tag resolves to products as: **products in `category_id` (and its descendants) that have `attribute_id`** — the same matching rule the category page uses (`product_attribute` pivot). See `AGENTS.md` → Catalog filtering.

> The documented schema has only `content`, no `title` / `description` / `no_index` / `canonical`. SEO meta would either reuse `name`/`content` or the schema must be extended in admin. Decide before building (open question below).

## What tags are good for

- Themed/filtered **landing pages** that need their own URL and SEO content.
- A **link target** for promo banners and menu items (banner "تجهیزات گیمینگ" → `/tags/gaming-gear`).

## What to use instead (not tags)

- **Best-sellers / newest rows** → dynamic queries (already `GetProductRows`).
- **Banners / sliders / header menu** → `banners`, `sliders`/`slides`, `menus`/`menu_items` (already built).
- **Hand-picked cross-category collections** → would require a new `product_tag` pivot (admin schema change). Only add if editorial collections are actually needed.

## Responsibilities

**Admin (`admin/`) — owns the schema:**
- Migration for `tags` (and any extra SEO columns if chosen).
- `Tag` model + Filament resource: manage slug, name, category, attribute, content, type.

**Shop (`shop/`) — read/render only:**
- `Tag` read model mapping the shared table.
- Route `GET /tags/{slug}` → thin `TagController` → action that loads products (category + descendants, filtered by the tag's attribute) reusing the category listing pieces.
- `Tags/Show.vue` reusing `ProductCard` / `Pagination` / filters where it makes sense.
- SEO: unique title/description, canonical, JSON-LD `BreadcrumbList`; breadcrumbs Home → Category → Tag.

## Build order (when approved)

1. Admin: migration → `Tag` model + factory + seeder → Filament resource.
2. Shop: `Tag` model → action + thin controller + Inertia page → feature tests.
3. Wire banners/menu items to `/tags/{slug}`.

## Open questions

- **SEO fields**: reuse `name`/`content`, or extend the schema with `title`/`description`/`no_index`/`canonical` (matches how `categories`/`products` do SEO)?
- **`type`**: single-vendor store has no sellers — is this column needed in shop, or admin-only?
- **One attribute per tag** (as the schema implies) vs. multiple — confirm before building.
