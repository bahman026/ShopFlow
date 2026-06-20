# ShopFlow Cache Register

Tracks cache keys that have been identified but are not yet implemented.
When a cache is implemented, move it to the **Implemented** section and record the key, driver, TTL, and where it is invalidated.

Legend: `[ ]` not started, `[x]` implemented.

> **Note:** `variety_counts` on `products` is NOT a cache. It is a denormalized DB column kept in sync by `Variety::booted()` (saved/deleted events calling `syncProductVarietyCount()`). No cache entry needed for it.

---

## Pending (identified, not implemented)

| # | Cache key / pattern | What it caches | Suggested TTL | Invalidated when |
|---|---------------------|----------------|---------------|-----------------|
| 1 | `categories.tree` | Full nested category tree | 1 hour | Category saved / deleted |
| 2 | `banners.{position}` | Published banners for a given position | 30 min | Banner saved / deleted |
| 8 | `sliders.{position}` | Published slider with its slides for a given position | 30 min | Slider or Slide saved / deleted |
| 3 | `menus.{slug}` | Rendered menu tree for a given slug | 1 hour | Menu or MenuItem saved / deleted |
| 4 | `attributes.group.{group_id}` | Attributes belonging to a group | 1 hour | Attribute saved / deleted |
| 5 | `products.slug.{slug}` | Single product record (with images, attributes, brand, category) | 30 min | Product saved / deleted |
| 6 | `products.category.{category_id}` | Published products list for a category page | 15 min | Product saved / deleted |
| 7 | `varieties.product.{product_id}` | All varieties for a product (price, inventory, status) | 15 min | Variety saved / deleted |

---

## Implemented

_None yet._

---

## Rules

- Cache keys use dot notation and include any dynamic segment as `{placeholder}`.
- Tag caches by entity name so bulk-invalidation is possible (e.g. `Cache::tags(['banners'])->flush()`).
- Invalidation logic goes in the model's `booted()` method or a dedicated observer, never in controllers or resources.
- Document every new key here before or when implementing it.
