<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- Use Pest directly, not `php artisan test`.
- To run all tests: `vendor/bin/pest`.
- To run all tests in a file: `vendor/bin/pest tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `vendor/bin/pest --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>

# ShopFlow Storefront Conventions

This is `shop/`, the customer-facing storefront (Laravel 13 + Inertia). The Filament admin panel lives in `admin/`. Both apps share the same PostgreSQL database and schema. `docs/ShoFlow db doc.md` is the source of truth for tables and relationships. All PHP files use `declare(strict_types=1);` and are formatted by Pint.

## Read the docs first (before doing anything)

- **Before starting any task, read the files in `docs/`** — at minimum `ShoFlow db doc.md` (schema, source of truth), `STOREFRONT_IMPLEMENTATION.md` (the storefront roadmap: what to build and in what order), `ORDER.md` (orders & inventory rules), and `CACHE.md` (cache keys). `IMPLEMENTATION.md` tracks the admin build, not the storefront. Do not write code before reading the docs relevant to the task.

## Running commands and tests

- The app runs in Docker. Execute commands inside the container: `docker exec -it -u www-data shop_flow_shop_app bash`.
- Before finalizing, run the full check suite: `composer test-dev`. It runs, in order: Pest, Pint (`--bail`), Pest type-coverage (`--min=100`), and PHPStan (level 5). All four must pass.
- For a quick single run, use `vendor/bin/pest --filter=testName` and `vendor/bin/pint` to fix formatting.
- 100% type coverage is required: add return types, parameter types, and property/PHPDoc types to every new PHP file.
- Commit with this author: `Bahman026 <bahman026@gmail.com>` (use `git commit --author="Bahman026 <bahman026@gmail.com>"`).
- Always ask before committing. NEVER commit without explicit user approval.
- Keep commits separate and atomic: one logical change per commit with its own message. Do not bundle unrelated changes into a single commit.

## Architecture

- Laravel + Inertia (server-driven SPA). Controllers return `Inertia::render(...)`; page components live under `resources/js/Pages`.
- **The database schema is owned by the admin app.** Do not recreate tables that already exist in `admin/`; add Eloquent models here that map to the shared tables. Coordinate any schema change in the admin app's migrations, then update `docs/ShoFlow db doc.md`.
- This is single-vendor commerce; the storefront reads catalog/pricing data and writes carts, orders, addresses, receipts/transactions per the documented rules.
- **Search runs behind the `App\Contracts\ProductSearch` contract** (bound to `DatabaseProductSearch` in `AppServiceProvider`), which does case-insensitive `ILIKE` matching now. Depend on the contract, never the implementation, so an Elasticsearch backend can be swapped in later without touching controllers/actions. It powers both the results page (`/search`) and the header autocomplete (`/search/suggest`).
- **Auth is mobile-first** (`AuthController`, routes under `/login`). OTP is the primary path (and registers on first login); password login is an alternative. Codes live in cache via `SendOtpCode`/`VerifyOtpCode` and are "sent" by a logged stub - replace with a real SMS provider later. The shared `users` table has NOT NULL `email`/`password` and a non-unique `mobile`, so OTP sign-ups seed placeholders (`User::placeholderEmail`) and a random password. `auth.user` + auth `flash` are shared in `HandleInertiaRequests`.
- **Account area** lives under `/account` (auth middleware, `AccountController`). `AccountLayout.vue` is the shared shell (sidebar nav + user card + logout) wrapping `AppLayout`; account pages are `noindex`. Built: dashboard (`Account/Dashboard.vue`) and profile edit (`Account/Profile.vue`, edits name/email; mobile read-only; placeholder email hidden via `User::hasPlaceholderEmail`). Not-yet-built sidebar links render `Account/ComingSoon.vue`. Profile saves flash a generic `status` message (shared in `HandleInertiaRequests`); `UserDTO` shapes the user payload.
- **Addresses** (`AddressController`, `/account/addresses`) are immutable history: editing creates a NEW row (`UpdateUserAddress`) that inherits `prime` and soft-deletes the old one (kept for order history, hidden from the active list). First address auto-primary; one primary per user via the model `saved` hook; any address can be promoted from the list (`setPrimary`, `PUT /account/addresses/{address}/primary`); delete is soft-only (`destroy`, `DELETE /account/addresses/{address}`) to preserve order history, and deleting the default promotes the newest remaining address. The shared table has no plate/unit columns, so those round-trip through `description` as JSON (`App\Support\AddressDescription`); recipient name uses the account name (no per-address column). Province/city are cascading (`/account/addresses-cities`). **Neshan maps**: the location (lat/long) is a section separate from the province/city selects. Two key types. Picking a point on either map sets lat/long and auto-fills the address via reverse geocoding (`ReverseGeocode`, `/account/addresses-reverse`, service key). With a `web.` map key (`services.neshan.map_key`, `NESHAN_MAP_KEY`, shared per-page) the form shows the interactive `NeshanMap.vue` (draggable marker, client-side tiles, fast). Without a `web.` key the form falls back to `MapPicker.vue`: a draggable Neshan static map (proxied `StaticMap` -> `/account/addresses-static`, service key) with a fixed center pin (the selected point is always the map center), drag-to-pan (pixel delta -> lat/long via Web Mercator) and zoom buttons; the proxy caches images (30 days) and fails gracefully on timeout (the static plan is slow, so the web key is preferred). All Neshan calls use the server-side `service.` key (`NESHAN_SERVICE_KEY`); only the `web.` key ever reaches the browser. Note: `service.` keys are IP-scoped in the Neshan panel, the server's (public) egress IP must be allowed. The nullable `latitude`/`longitude` columns live on the admin-owned `addresses` table (in the `create_addresses_table` migration).

## Frontend (Inertia + Vue)

The shop UI uses **Inertia + Vue 3** (SSR enabled). Clean, readable code is a hard requirement, not a nice-to-have.

- **Vue 3 only**, `<script setup>` with the Composition API. No Options API.
- **Component for every element and every page.** Build small, single-purpose components and compose them; do not write large monolithic pages.
  - `resources/js/Pages/` — one component per route (e.g. `Products/Index.vue`, `Products/Show.vue`).
  - `resources/js/Layouts/` — shared page layouts (header, footer, RTL shell).
  - `resources/js/Components/` — reusable UI elements (`BaseButton.vue`, `PriceTag.vue`, `ProductCard.vue`, `QuantityInput.vue`, ...). Each visual element is its own component.
- One component per file; name files `PascalCase.vue`. Keep components small and focused; if a component grows large, split it.
- Props are explicitly typed and validated; emit named events instead of mutating props. Keep business logic in the controller/server, components stay presentational.
- Extract repeated logic into composables under `resources/js/composables/` (e.g. `useCart.ts`).
- Style with Tailwind utility classes, RTL-first (see fonts/RTL section). No inline styles, no copy-pasted markup; reuse components.
- **Brand color is `#ff8615`.** It is registered in `resources/css/app.css` as `--color-brand`, so use the Tailwind `brand` utilities (`bg-brand`, `text-brand`, `border-brand`, ...) for primary actions and accents. Do not hardcode the hex in components.
- **Icons: FontAwesome only, for a uniform icon set.** Always render icons through the shared `<Icon>` component (`resources/js/Components/Icon.vue`). Never use raw SVGs, emoji, or another icon library, and do not place `<FontAwesomeIcon>` directly in components.
  - Register every icon as an object in `resources/js/fontawesome.js` (solid from `@fortawesome/free-solid-svg-icons`, brands from `@fortawesome/free-brands-svg-icons`) and pass the imported icon object: `<Icon :icon="..." />`.
  - Do NOT use string names with `library.add` (e.g. `['fab','instagram']`). Inertia turns FontAwesome's missing-icon `console.error` into an SSR exception, so string lookups break SSR. Passing icon objects is the SSR-safe, tree-shakeable pattern.
- Pages must use Inertia's `<Head>` for SEO tags (see SEO section) and render meaningful content server-side.
- **SSR runtime**: SSR is served by a Node process (`php artisan inertia:start-ssr`, port 13714). It has no auto-restart and Inertia's SSR server crashes the whole process on a bad request body (it `JSON.parse`s with no error handling), after which every page silently falls back to client-side (empty `<div id="app">`, no `data-server-rendered`). If pages stop rendering server-side, restart it and never POST malformed payloads to the render endpoint. After `npm run build`, restart SSR so it serves the new bundle.
- **Lint & format the frontend** (the JS/Vue equivalent of Pint/PHPStan):
  - **Prettier** formats `resources/js` (config in `.prettierrc.json`: 4-space indent, single quotes, semicolons, `printWidth` 100, Tailwind class sorting via `prettier-plugin-tailwindcss`).
  - **ESLint** (flat config in `eslint.config.js`: `eslint-plugin-vue` recommended + `@vue/eslint-config-prettier`) analyses and auto-fixes Vue/JS issues.
  - Scripts: `npm run format` (write) / `npm run format:check`, `npm run lint` / `npm run lint:fix`. Run them before finishing frontend work; `composer test-dev` also runs `lint` + `format:check`.

## Language, RTL & fonts

- **The storefront is Persian only.** There is no language switcher and no English UI. Set `<html lang="fa" dir="rtl">` in the root template, and write all user-facing text in Persian.
- Build RTL-first: use Tailwind logical utilities (`ms-`, `me-`, `ps-`, `pe-`, `start-`, `end-`) instead of left/right so layout flows right to left.
- **Font: `A Iranian Sans` (IranSans).** Reuse the same font as the admin app. The file lives in admin at `public/fonts/AIranianSans.ttf`; copy it into this app's `public/fonts/AIranianSans.ttf` and load it with an `@font-face` (family name `A Iranian Sans`), then set it as the default `font-family` on `body`.
- Show Persian digits and Jalali (Shamsi) dates. Format on the server so SSR output is already correct.

## SEO (very high priority)

SEO is critical for this storefront. Treat it as a first-class requirement on every page, not an afterthought.

- Render pages with SSR so the full Persian HTML is present in the initial response and indexable. Never rely on client-only rendering for content that must rank.
- Every page sets a unique, descriptive `<title>` and meta description. Use the `<Head>` component from Inertia so they appear in the SSR output.
- Add Open Graph and Twitter card tags, and a canonical URL on every page.
- Use clean, human-readable Persian slugs in URLs; keep them stable (do not change a slug once published).
- Use one `<h1>` per page and a correct heading hierarchy; use semantic HTML (`<main>`, `<nav>`, `<article>`).
- Add JSON-LD structured data: `Product` (with `Offer`/price/availability) on product pages, `BreadcrumbList` on listings, and `Organization`/`WebSite` on the home page.
- Serve a `sitemap.xml` and `robots.txt`; return correct HTTP status codes (200 / 404 / 301) so crawlers see the right signals.
- Set `lang="fa"` and provide descriptive `alt` text (in Persian) on images.

## Implementation order

When adding a new feature, build files in this order, matching existing files:

1. Migration (only when a genuinely new table is needed — most already exist via admin)
2. Model, factory, seeder
3. DTOs + Actions for the page/endpoint data, then a thin Controller + Inertia page (or Eloquent API Resource for JSON endpoints)
4. Test

## Server-side structure (controllers, actions, DTOs)

Keep controllers thin and push logic into single-purpose actions that return typed DTOs. See `ProductController` + `app/Actions/Product/*` + `app/DTOs/*` as the reference.

- **Controllers** only resolve the request: load the model(s), call actions, and hand the result to `Inertia::render(...)`. No payload shaping or business logic in the controller. Inject actions via method (or constructor) parameters; the container resolves them.
- **Actions** live in `app/Actions/<Area>/` (e.g. `Actions/Catalog`, `Actions/Product`), one responsibility per class, invoked via `__invoke(...)`. Reusable, cross-page actions (image/price shaping) go under `Actions/Catalog`; page-specific ones under their feature folder. Actions depend on other actions through constructor injection.
- **DTOs** live in `app/DTOs/`, **one per model** (`ProductDTO`, `VarietyDTO`, `ImageDTO`, `ReviewDTO`, ...). They are `readonly` classes using constructor property promotion with `camelCase` properties that match the Inertia/JSON keys the frontend expects.
  - Do not create DTOs for small value shapes (prices, links, breadcrumbs, variant axes/options). Type those as PHPDoc array shapes instead, e.g. `array{price: int, salePrice: int|null, discountPercent: int|null}` or `array{heading: string, url: string}`.
  - Provide a `toArray(): array` for the Inertia boundary. Flat DTOs may use `get_object_vars($this)`; DTOs holding nested DTOs convert them explicitly in `toArray()`. Add a `fromArray(array $data): self` only when something actually hydrates the DTO from an array (e.g. cache payloads, queue jobs) — don't add it speculatively.
  - Actions return DTOs (or plain typed arrays for value shapes / lightweight cards); the controller calls `->toArray()` on DTOs at the Inertia boundary so the frontend receives plain nested arrays. Do not pass DTO objects straight into `Inertia::render` (Inertia testing reads array keys, not object properties).
- **Type every query closure** (100% type coverage requires it). Eager-load constraints inside `with([...])` receive a `Illuminate\Database\Eloquent\Relations\Relation` — type the param as `Relation` and use base query methods. Larastan can't resolve model scopes (`published()`, `active()`) on a bare `Relation`, so inline the filter instead, e.g. `fn (Relation $query) => $query->where('status', VarietyStatusEnum::PUBLISHED->value)`. `tap()` closures get a `Builder`; `map()`/`filter()`/`each()` over an Eloquent collection get the model type (`fn (Variety $variety) => ...`).

## Models

- Declare `protected $fillable` (array) and `protected $casts` (array property; `User` uses the `casts()` method).
- Cast enum columns to the enum class: `'status' => ProductStatusEnum::class`. Cast booleans to `'boolean'`.
- Add a class-level PHPDoc block listing every `@property` (including relations as `Collection<Model>` or `Model|null`).
- Type all relationship methods with their return type (`HasMany`, `BelongsTo`, `MorphOne`, `MorphMany`, `BelongsToMany`).
- Query scopes are typed: `public function scopePublished(Builder $query): Builder`.
- Model-event logic goes in `booted()`.

## Enums

- Backed enums (usually `int`) in `app/Enums/`, e.g. `ProductStatusEnum: int` with explicit case values (`DELETED = 10`).
- Provide `label(): string` and, where shown to users, `color(): string`, both using `match ($this)`.
- Mirror the admin app's enum values so both apps agree on the shared schema.

## Migrations

- Anonymous class style: `return new class extends Migration`.
- Use `$table->foreignIdFor(Model::class)` for foreign keys (add `->nullable()` when optional). Chain `->constrained()->cascadeOnDelete()` / `->nullOnDelete()` / `->restrictOnDelete()`.
- For a second FK to the same table, use a named column: `$table->foreignId('user_creator_id')->nullable()->constrained('users')->nullOnDelete()`.
- Pivot tables add `$table->unique([...])` on the key pair and `->cascadeOnDelete()` on both FKs.
- **Pivot naming**: Laravel derives the name alphabetically from the two singular model names (e.g. `Attribute` + `Variety` → `attribute_variety`).
- Default enum columns to a case value: `$table->unsignedTinyInteger('status')->default(ProductStatusEnum::PUBLISHED->value);`.
- When a column references a table that does not exist yet, add it as a plain nullable column with no FK constraint, and add the real FK later.
- Always implement `down()` with `Schema::dropIfExists(...)`.

## Factories

- `@extends Factory<Model>` PHPDoc, `definition(): array` with `@return array<string, mixed>`.
- Use the `fake()` helper (not `$this->faker`).
- Derive dependent fields with closures: `'slug' => fn (array $attributes): string => Str::slug($attributes['heading'])`.
- Random enum values via `fake()->randomElement(SomeEnum::cases())`.
- Optional related data goes in named states using `afterCreating()` (e.g. `withImage()`).
- For unique slug fields, prefer `Str::uuid()` over `fake()->unique()` to avoid cross-test collisions.

## Seeders

- `DatabaseSeeder::run()` calls all seeders via `$this->call([...])` in dependency order; it holds only necessary/reference data.
- Keep factory-generated sample data in a separate `TestSeeder`, not in `DatabaseSeeder`.
- Reference seeders use idempotent `updateOrCreate()` / `firstOrCreate()`.
- When truncating a table whose model has a `deleting` event, delete records one by one via `Model::all()->each->delete()` (on a Collection, not a query builder).

## Tests

- This project uses **Pest** (not PHPUnit — this overrides the auto-generated boost note above). Write tests as Pest functions (`it(...)`, `test(...)`, `expect(...)`) with `declare(strict_types=1);`. Create them with `php artisan make:test --pest {name}`. Run them with `vendor/bin/pest`, not `php artisan test`.
- Global setup lives in `tests/Pest.php`: `Feature` tests use `TestCase` + `DatabaseTransactions` (each test runs in a transaction that is rolled back).
- **Shared database, not sqlite.** The shop is a read-only consumer of the admin-owned schema, so tests run against a real Postgres test database (`shop_flow_test`) whose schema is built by **admin's** migrations — never the shop's. The shop must not own or migrate those tables. Do not switch tests back to sqlite/`RefreshDatabase`; that hides schema drift from production.
- **One-time local setup** (run from the admin container, pointing at the test DB):

```bash
createdb -U shop_flow shop_flow_test   # or CREATE DATABASE shop_flow_test;
cd admin
DB_DATABASE=shop_flow_test php artisan migrate --force
DB_DATABASE=shop_flow_test php artisan db:seed --class="Database\Seeders\SettingSeeder" --force
```

- Most tests should be feature tests. Build test rows with factories inside the test (they roll back via the transaction). Assert with `assertDatabaseHas(...)`, `assertModelMissing(...)`, or `expect($model->refresh())`.
- Run the minimum tests needed with a filter before finalizing, then `composer test-dev` for the full suite.

## Business constraints

- **No seller / marketplace system.** ShopFlow is a single-vendor store. Never add `seller_id`, `seller_creator_id`, or any seller relation to models, migrations, or controllers.
- **Inventory**: stock is decremented only on successful payment (Strategy A); carts never change inventory. See `docs/ORDER.md`.
- **Quantity cap**: the customer can never choose a quantity above the selected variety's available stock. The quantity input on the product/cart pages must clamp to the variety `inventory`; the add-to-cart action must reject amounts beyond it.
- **Catalog filtering**: attribute filters on the category page match products through the `product_attribute` pivot (`Product::attributes()`) — the schema's documented "filters to products" link — never through `varieties`/`attribute_variety` (those drive the product page's variety selector). `attribute_group_category.as_filter` decides which groups appear as filters (resolved across the category and its descendants). Facet within a group is OR, across groups is AND.
  - Facets only list values actually attached to products in the category, and each option carries a product `count` (category-level baseline, not recomputed against the other active selections).
  - An availability filter (`in_stock` → `products.has_stock`) and a price range (on `products.price`, the denormalized cheapest-variety base price) are also supported.
  - Filter UI is Digikala-style (`CategoryFilters.vue`): availability toggle, price range slider, brand list with a search box, collapsible accordion sections, per-option counts, and instant apply on change.
- **Product gallery**: show all images together — the product images plus every variety image, combined and deduped by URL. Never hide images based on the selection; selecting a variety only switches the main image to that variety's photo (when it exists in the list).
- **Product cards**: the card image is the product's `featuredImage`, falling back to the first variety image so cards still show a photo when the product has no product-level image (eager-load `varieties.image` wherever cards are built to avoid N+1). Cards link via `AppLink` with `new-tab` so products open in a new tab.
- **Addresses are immutable history.** Editing an address creates a new record (inheriting the edited one's primary status); addresses are never deleted, so orders keep an accurate history.

## Roadmap & docs

- **Before starting any task**, read these files (copied under `docs/`):
  - `ShoFlow db doc.md` — the full schema reference; the source of truth for columns and relationships.
  - `STOREFRONT_IMPLEMENTATION.md` — the storefront roadmap; what to build and in what order. Update it as features land.
  - `IMPLEMENTATION.md` — the admin build status (reference only; not the storefront plan).
  - `ORDER.md` — orders/inventory rules.
  - `CACHE.md` — identified cache keys.
- Keep this "ShopFlow Storefront Conventions" section updated whenever a new reusable pattern is introduced.
