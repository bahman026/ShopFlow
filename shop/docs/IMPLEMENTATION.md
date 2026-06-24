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
- [x] Discounts (auto-applied price rules per variety)
- [x] Coupons (+ `coupon_product`, `coupon_variety`, `category_coupon` scoping pivots)
- [x] Images (polymorphic, used via uploads - no standalone resource by design)
- [x] Banners (polymorphic images via `images` table)
- [x] Sliders + Slides (each slide has one polymorphic image via `images` table)
- [x] Menus + Menu Items (nested via `parent_id`; optional polymorphic image per item)
- [x] Pages (CMS static pages; polymorphic image; SCHEDULED status with `published_at`)
- [x] FAQs (question + answer; `order` and nullable `position` for placement context)
- [x] Reviews (user reviews for products; `parent_id` for replies; `status` moderation)
- [x] Wishlists (`user_id` + `product_id` pivot; cascades on user/product delete; list + delete resource)
- [x] Addresses (immutable history: model, factory, seeder, Filament resource (+ pages), tests. Editing creates a NEW address instead of mutating; the new address inherits the edited one's primary status; records are never deleted, so orders keep an accurate address history. One primary per user enforced via a model `saved` hook)

Sample data for manual admin testing lives in `TestSeeder` (`php artisan db:seed --class=TestSeeder`); `DatabaseSeeder` holds only necessary data.

Cross-cutting improvements landed:
- Navigation groups reorganized: Catalog / Promotions / Attribute / Content / Address / Logistics.
- `CACHE.md` added to track identified-but-not-implemented cache keys.
- `ColorPicker` added to Variety form; `ColorColumn` in the table. Auto-fill from attribute only triggers when `attribute_id` changes, preserving manual overrides.
- Documentation reorganized into `docs/` directory (`ShoFlow db doc.md`, `VARIETY_GUIDE.md`, `IMPLEMENTATION.md`, `CACHE.md`, `ORDER.md`).
- **Inventory rule** (`ORDER.md`): stock is decremented only on successful payment (Strategy A); carts never change `varieties.inventory`.
- **Localisation (fa / en)**: `SetLocale` middleware + `/locale/{locale}` route + user-menu switcher. All resources (Brand, Category, Product, Variety, City, Province, Coupon, Discount, ShippingLine, ShippingMethod, ShippingCity, Ancestor, AttributeGroup, AttributeGroupCategory, Attribute, Slider, Slide, Banner, Menu, MenuItem, Page, FAQ, Review, Wishlist, User) have `lang/en` + `lang/fa` files, `trans()`-based labels on all form fields and table columns, and translated enum `label()` methods. List-page subheadings are set via `mount()`. Filament vendor translations published for built-in UI strings.
- **Persian font**: `A Iranian Sans` loaded from `public/fonts/AIranianSans.ttf`; applied globally when locale is `fa` via `public/css/persian-font.css` and a `renderHook` in `AdminPanelProvider`.
- **Locale switching** (`en` / `fa`): `SetLocale` middleware reads the locale from session and calls `App::setLocale()`. A `/locale/{locale}` route stores the choice. Two user-menu items (English / فارسی) in `AdminPanelProvider` let admins switch. All Filament sub-package translations (`filament`, `filament-forms`, `filament-tables`, `filament-actions`, `filament-notifications`) are published to `lang/vendor/`.

## Phase 0 - Finish current branch (`implement_variety`)

Done. Variety has model, migration, factory, resource (+ pages), tests, and `variety_counts` auto-sync.

- [x] Fix `VarietyResource` table: `product.heading` column (was `product.title` with `->numeric()` on a string)
- [x] `attribute_id` FK on varieties - links each variety to one attribute; auto-populates `attribute_value` and `color` from the attribute on save
- [x] `attribute_variety` pivot — each variety can link to multiple attributes from different groups (e.g. Size as primary + Color via pivot); "Additional Attributes" multi-select added to both VarietyResource and the ProductResource inline variety repeater
- [x] `attribute_group_id` in ProductResource filtered by selected category (via `attribute_group_category`); changing category clears the group selection
- [ ] Variety extensions (`warehouse_id`, `guarantee_name_id`, `variety_serials`, `variety_details`) - deferred to Phase 3, they need Warehouses / Guarantees first
- [x] Attribute `required` flag enforced in `ProductResource`: if a category has required attribute groups, saving a product without them shows a danger notification and skips the sync

## Phase 1 - Pricing & promotions (recommended next)

Builds on Products/Varieties; prerequisite for Orders.

- [x] Discounts (auto-applied price rules per variety)
- [x] Coupons (+ `coupon_product`, `coupon_variety`, `category_coupon` scoping pivots)
- [ ] Cron jobs for Discounts & Coupons:
  - Auto-expire discounts when `ended_at` is passed (set status or filter in queries)
  - Auto-expire coupons when `expired_at` is passed
  - Optionally: reset `sold` / `total_used` counters on a schedule if needed

## Phase 2 - CMS / content (independent quick wins)

Depend mostly on Images only.

- [x] Banners
- [x] Sliders + Slides
- [x] Menus + Menu Items
- [x] Pages
- [x] FAQs
- [x] Reviews
- [x] Wishlists
- [ ] Tags
- [ ] Brand-Category pages
- [ ] Redirects
- [ ] Helps
- [ ] Holidays

## Phase 3 - Inventory & logistics (Orders prerequisites)

- [ ] Warehouses (unblocks variety `warehouse_id`)
- [x] Shipping Lines (carrier table; `name` + `cost`)
- [x] Shipping Methods (`shipping_line_id` FK to Shipping Lines; service tier with rules)
- [x] Shipping Cities (per-city cost and availability overrides per shipping method)
- [ ] Guarantee Names
- [x] `attribute_variety` pivot (done — moved up from Phase 3)
- [ ] Variety extensions: `variety_serials`, `variety_details`

## Phase 4 - Commerce core

The main goal; depends on most of phases 1-3.

- [~] Carts (migration, model, factory, seeder, tests; no Filament resource by design. Each row is one line item: variety + count, per user or guest session. Inventory rule in `ORDER.md`)
- [x] Orders (`orders` table only: model, migration, factory, seeder, Filament resource (+ pages), tests. `OrderStatusEnum` + `OrderSrcEnum`; staff/finance refs to not-yet-built tables kept nullable without FK. Inventory rule in `ORDER.md`)
- [x] `order_varieties` (line items: model, migration, factory, seeder, Filament resource (+ pages), tests. Stores a per-line price/discount snapshot; `sub_order_id` kept nullable without FK since `sub_orders` is not implemented. An order's many varieties are also editable inline on the Order edit page via `OrderVarietiesRelationManager`)
- [~] Sub Orders + `sub_order_logs` — NOT IMPLEMENTED (single-vendor). These are seller-centric; with no sellers an order maps 1:1 to fulfillment, so they add no value. Fulfillment lives on the order itself / `order_shippings`.
- [x] `order_shippings` (fulfillment/shipment records: model, migration, factory, seeder, `OrderShippingPaymentTypeEnum`, Filament resource (+ pages), inline relation manager on Order, tests)
- [x] `order_notes` (internal staff notes: model, migration, factory, seeder, Filament resource (+ pages), inline relation manager on Order, tests)
- [~] `order_logs`, `order_call_logs` — NOT IMPLEMENTED (not needed for current scope)
- [x] Receipts (payment receipts: model, migration, factory, seeder, `ReceiptTypeEnum`, polymorphic image, Filament resource (+ pages), inline relation manager on Order, tests. `card_id` kept nullable without FK since cards are not built)
- [x] Transactions (online gateway payments: model, migration, `TransactionPortEnum` (Mellat/Parsian/Zarinpal), `TransactionStatusEnum`, factory, seeder, Filament resource (+ pages), inline relation manager on Order, tests. `accounting_id` kept nullable without FK since accounting is not built)
- [x] Gateways (gateway config: model, migration, `GatewayForEnum`, factory, seeder, polymorphic image, encrypted password, Filament resource (+ pages), tests)

## Phase 5 - Support, warranty & accounting

- [ ] Tickets + `ticket_messages` + `ticket_attachments` + `ticket_permissions`
- [ ] Guarantees + `guarantee_items` + `guarantee_item_images` + `guarantee_logs` + `guarantee_item_logs`
- [ ] Accounting Sources
- [ ] Persons
- [ ] Settings
- [ ] Cards / Saved Cards

## Phase 6 - Users extended & misc

- [x] `user_configs` (per-user key/value settings: model, migration, factory, seeder, Filament resource (+ pages), tests. `cascadeOnDelete` with the user; unique on `user_id`+`key`)
- [~] `user_roles`, `user_permissions` — NOT NEEDED (covered by spatie's own user↔role / user↔permission pivots; their `seller_id` is irrelevant single-vendor)
- [ ] `user_sources` — defer to accounting Sources (Phase 5)
- [ ] `user_statuses` (per-user status restriction) — niche, not needed now
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
