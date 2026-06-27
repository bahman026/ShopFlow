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
- Keep controllers thin: push data loading/shaping into single-purpose actions (`app/Actions`) that return typed DTOs (`app/DTOs`, one per model). See `ProductController`/`HomeController` + their actions. Details in `AGENTS.md`.
- Before finishing, run `composer test-dev` (Pest, Pint, 100% type coverage, PHPStan level 5, ESLint, Prettier). CI runs the same checks.

---

## Phase 0 - Foundation

- [x] Inertia + Vue 3 + SSR wired and verified
- [x] RTL Persian root template (`lang="fa" dir="rtl"`)
- [x] `A Iranian Sans` font + brand color `#ff8615`
- [x] Base `AppLayout` + sample `Home` page
- [x] Eloquent models mapping shared tables (read-focused): `Category`, `Brand`, `Product`, `Variety`, `Attribute`, `Image`, `Banner`, `Slider`/`Slide`, `Menu`/`MenuItem`, `Page`, `Faq`, `Review` (+ status enums and `HasOptions` trait). Relations to not-yet-created models (`AttributeGroup`, `Coupon`, `Discount`) are deferred to their phases.
- [~] Shared UI kit components: `BaseButton` + `AppLink` (supports `new-tab` + external-link detection) + `Icon` + `PriceTag` + `ProductCard` (image falls back to first variety image when the product has none; opens in a new tab) + `SectionHeading` + `Breadcrumbs` + `RatingStars` + `Pagination` + `EmptyState` done; `QuantityInput` pending
- [x] Helpers/composables: Persian digits, Jalali date, money formatting (`useFormat`)
- [x] SEO scaffolding: per-page `<Head>` via `AppHead` component, shared meta defaults (Inertia `seo` shared prop), canonical URL, Open Graph + Twitter, `robots.txt`
- [x] Error pages (404 / 500) in Persian, RTL (self-contained Blade, brand color, IranSans, no Vite/SSR dependency)
- [x] Shared database connection: shop reads the admin-owned Postgres (`shop_flow_db`); file/sync session/cache/queue so shop owns no tables (`.env.example` stays SQLite for CI/tests)
- [x] Icon system: FontAwesome (self-hosted via npm) behind the shared `Icon` component, icon-object pattern (SSR-safe). See `AGENTS.md`
- [x] Global footer wired from `settings`: read-only `Setting` model + Inertia shared `footer` (link columns, contact, socials, about, copyright) rendered by `AppFooter`/`Footer/*` components
- [x] Server-side structure: thin controllers delegating to actions (`app/Actions/{Catalog,Product,Home}`) returning per-model DTOs (`app/DTOs`)
- [x] Dev tooling: `composer test-dev` runs Pest, Pint, Pest type-coverage (`--min=100`), PHPStan (level 5), ESLint + Prettier (`resources/js`); mirrored in CI (`deploy-application.yml`)

## Phase 1 - Catalog browsing (highest SEO value, build first)

Read-only catalog. This is where SEO and SSR matter most.

- [x] Home page: `HomeController` + `Home.vue` with hero slider, category strip, promo banner grid, product carousels (newest + most viewed), selected brands; JSON-LD `Organization`/`WebSite`; graceful empty states; feature tests. Caching (`CACHE.md` keys 1, 2, 8, 3) deferred to Phase 6
  - Header (`AppHeader` + `Header/*`): logo, search, account/cart actions, desktop category menu with dropdowns, mobile drawer; categories shared via Inertia `nav.categories`
  - `Variety` read model exposes a polymorphic `image` relation (per-color photo) for the upcoming product detail page
- [x] Category listing page: `CategoryController@show` (`/categories/{slug}`) + `Category/Show.vue`. Lists the category's products plus its descendants', with facet filters (brand, attribute groups marked `as_filter`, price range), sorting (newest/cheapest/expensive/popular) and pagination; sidebar filters + toolbar + `Pagination`/`EmptyState` components; breadcrumbs; JSON-LD `BreadcrumbList`; feature tests. Attribute filtering matches products through the `product_attribute` pivot (the documented "filters to products" link, NOT varieties); facets list only attribute values actually attached to products in the category; OR within a group, AND across groups. Price filters on `products.price` (denormalized cheapest-variety base price). Filter UI is Digikala-style: availability toggle (`in_stock` → `products.has_stock`), price range slider, brand list with search box, collapsible accordion sections, per-option product counts, and instant apply on change
- [x] Product detail page: `ProductController@show` (`/products/{slug}`) + `Product/Show.vue`. Gallery shows all images combined (product images + every variety image, deduped by URL); selecting a variety switches the main image to that variety's photo without hiding the others. Variety selector (primary attribute group drives selection, additional attributes constrained by it, never the other way), buy box (price hidden until a variety is fully selected; price/discount/stock, trust badges, quantity), specs, description, breadcrumbs, related carousel; JSON-LD `Product`/`Offer` + `BreadcrumbList`; view counter; feature tests. Add-to-cart wiring deferred to Phase 3
  - Quantity rule: the quantity stepper must never exceed the selected variety's `inventory` (clamp the max). Enforced with cart wiring in Phase 3
- [x] Product reviews (read) on the product page (approved only). Ratings summary deferred (no numeric rating field in `reviews`)
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
- [ ] Add to cart / update quantity / remove line. Quantity is capped at the variety's available `inventory` (clamp in the UI and reject over-limit amounts server-side)
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
