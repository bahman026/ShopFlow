# ShopFlow Admin - Implementation Roadmap

Tracks what is built and what remains, ordered by implementation priority. Scope and table definitions come from `ShoFlow db doc.md`. Status updates as work lands.

Legend: `[x]` done, `[~]` partial, `[ ]` not started.

Each entity is considered "done" when it has: model, migration, factory, Filament resource (+ pages), and Pest resource tests, following the patterns in `AGENTS.md`.

## Done

Catalog layer and platform basics.

- [x] Users (resource) + Roles & Permissions (spatie, `RolePermissionSeeder`)
- [x] Provinces
- [x] Cities
- [x] Categories
- [x] Brands
- [x] Ancestors
- [x] Attribute Groups
- [x] Attributes
- [x] Attribute-Group-Categories
- [x] Products (+ `product_attribute` pivot, product images)
- [x] Varieties (+ `variety_counts` auto-sync on Product)
- [x] Images (polymorphic, used via uploads - no standalone resource by design)
- [~] Addresses (model only, no resource yet)

## Phase 0 - Finish current branch (`implement_variety`)

Done. Variety has model, migration, factory, resource (+ pages), tests, and `variety_counts` auto-sync.

- [x] Fix `VarietyResource` table: `product.heading` column (was `product.title` with `->numeric()` on a string)
- [ ] Variety extensions (`warehouse_id`, `guarantee_name_id`, `attribute_id`, `variety_attribute`, `variety_serials`, `variety_details`) - deferred to Phase 3, they need Warehouses / Guarantees first
- [ ] Attribute `as_filter` / `required` flags into the product/category flow (open `// todo` in the attribute-group-category migration) - moved to its own branch; it is an attribute/category feature, not variety

## Phase 1 - Pricing & promotions (recommended next)

Builds on Products/Varieties; prerequisite for Orders.

- [ ] Discounts (auto-applied price rules per variety)
- [ ] Coupons
- [ ] `coupon_product`
- [ ] `coupon_variety`
- [ ] `category_coupon`

## Phase 2 - CMS / content (independent quick wins)

Depend mostly on Images only.

- [ ] Banners
- [ ] Sliders + Slides
- [ ] Menus + Menu Items
- [ ] Pages
- [ ] FAQs
- [ ] Reviews
- [ ] Wishlists
- [ ] Tags
- [ ] Brand-Category pages
- [ ] Redirects
- [ ] Helps
- [ ] Holidays

## Phase 3 - Inventory & logistics (Orders prerequisites)

- [ ] Warehouses (unblocks variety `warehouse_id`)
- [ ] Shipping Lines
- [ ] Shipping Methods
- [ ] Shipping Cities
- [ ] Guarantee Names
- [ ] Variety extensions: `variety_attribute`, `variety_serials`, `variety_details`

## Phase 4 - Commerce core

The main goal; depends on most of phases 1-3.

- [ ] Carts
- [ ] Orders
- [ ] `order_varieties`
- [ ] Sub Orders + `sub_order_logs`
- [ ] `order_logs`, `order_shippings`, `order_call_logs`, `order_notes`
- [ ] Receipts
- [ ] Transactions
- [ ] Gateways

## Phase 5 - Support, warranty & accounting

- [ ] Tickets + `ticket_messages` + `ticket_attachments` + `ticket_permissions`
- [ ] Guarantees + `guarantee_items` + `guarantee_item_images` + `guarantee_logs` + `guarantee_item_logs`
- [ ] Accounting Sources
- [ ] Persons
- [ ] Settings
- [ ] Cards / Saved Cards

## Phase 6 - Users extended & misc

- [ ] `user_configs`, `user_roles`, `user_sources`, `user_statuses`, `user_permissions`
- [ ] Category Partner, Partner Requests, Organizational Requests, Contact Us
- [ ] Points, Newsletters, Notifications, System Notifications
- [ ] `email_histories`, `mobile_histories`, `mobile_password_resets`
- [ ] Working Hours
- [ ] `user_category_percent`, `user_price_conditions`
- [ ] eMalls Products, Short URLs, Bank SMS

## Future (per doc)

- [ ] Customer Club
- [ ] CRM
- [ ] Check Receipt section
- [ ] DigiKala Orders section
- [ ] Warranty repair cost change section
- [ ] `transaction_details`, `wallets` (under review)

---

Keep `AGENTS.md` in sync: when a new convention or pattern appears (or an entity is finished), update the "ShopFlow Admin Conventions" section there.
