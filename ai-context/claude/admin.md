# ShopFlow Admin Conventions

Project-specific patterns. Match these when adding or editing code. All PHP files use `declare(strict_types=1);` and are formatted by Pint (`vendor/bin/pint`).

## Running commands and tests

Commit rules, the shared database and the Docker basics are in `ai-context/claude/CLAUDE.md`.

- The app runs in Docker. Execute commands inside the container: `docker exec -it -u www-data shop_flow_admin_app bash`.
- Before committing, run `composer test-dev` (Pest, Pint, type coverage, PHPStan) inside the container and make sure it passes.

## Authorization

- Every Filament resource is gated by `App\Traits\AuthorizesWithPermissions` and declares a
  `PermissionGroupEnum` (catalog / content / orders / customers / shipping / marketing / settings).
  Permissions are `{view,create,update,delete}_{group}`, seeded by `RolePermissionSeeder`.
- **A new resource must declare `permissionGroup()`** — `ResourcePermissionsTest` fails the build otherwise,
  because an ungated resource would be reachable by anyone who can open the panel.
- Where a model has a policy it still applies *on top of* the permission, but only for the abilities the
  policy actually implements (Laravel denies any ability a policy omits, which would otherwise forbid
  viewing categories just because `CategoryPolicy` defines only `delete`).
- `super-admin` holds everything. `admin` runs catalogue/content/promotions fully, and processes orders,
  shipping and customers without delete. Settings, gateways and staff accounts are super-admin only.
- `User::canAccessPanel()` keeps storefront customers out of the panel entirely — the two apps share the
  `users` table.

## Operational dashboards (Pulse + log viewer)

- **`/pulse`** (Laravel Pulse) and **`/log-viewer`** (opcodesio/log-viewer) live in this app, both
  gated to `super-admin` by `viewPulse` / `viewLogViewer` in `AppServiceProvider`. Neither is a
  Filament resource, so `AuthorizesWithPermissions` does not reach them — those two gates are the
  only thing in front of them, and both dashboards show slow queries, exception messages and full
  stack traces. `OperationalDashboardsTest` fails the build if either opens up.
- **Pulse records from both apps into the same `pulse_*` tables**, because the two apps share one
  database. The storefront has the package too, but `config/pulse.php` there sets `'path' => null`
  so no `/pulse` route is registered on a public site. Migrations live here only — admin owns the
  schema, as always.
- The **`pulse` service** in `compose.prod.yaml` runs `pulse:check`, which is what fills the Servers
  card (CPU/memory/disk); the other cards are recorded by the apps as they serve requests.
  `pulse:trim` prunes old samples and runs from the scheduler, which sits behind the `workers`
  profile — until that profile is started the `pulse_*` tables only grow.
- **Logging is `stack` = `stderr,shared` in production.** `stderr` keeps `docker logs` and Docker's
  rotation working exactly as before; `shared` (a `daily` channel at `LOG_SHARED_PATH`) writes the
  file the viewer reads. The two apps write into one **named volume** (`applogs` at
  `/var/log/shopflow`), each in its own subdirectory, because the panel cannot see into another
  container. The viewer shows them as two folders, `Admin panel` and `Storefront`.
- **Both Dockerfiles create `/var/log/shopflow` owned by `www-data`.** Docker initialises a fresh
  named volume from the image, ownership included; left to Docker the volume is `root:root` and
  php-fpm silently writes no logs at all.

## Implementation order

When adding a new entity, build the files in this order, matching the existing files:

1. Migration
2. Model, factory, seeder
3. Filament resource
4. Pest test file

## Filament (v5)

- Resources live in `app/Filament/Resources/{Name}Resource.php`. Page classes live in `app/Filament/Resources/{Name}Resource/Pages/`.
- Static properties use the v5 union types:
  - `protected static ?string $model = Product::class;`
  - `protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';`
- **Do NOT use the `$navigationGroup` static property.** Override `getNavigationGroup()`, `getModelLabel()`, and `getPluralModelLabel()` as methods that call `trans()` so labels switch with the active locale (see `BrandResource`, `CategoryResource`).
- Forms use the schema signature: `public static function form(Schema $schema): Schema` returning `$schema->components([...])`. Import `Filament\Schemas\Schema`.
- Tables use `public static function table(Table $table): Table` with `->columns([])`, `->filters([])`, `->recordActions([...])`, `->toolbarActions([...])`.
- Actions come from the `Filament\Actions\` namespace (`EditAction`, `CreateAction`, `DeleteAction`, `BulkActionGroup`, `DeleteBulkAction`).
- Import individual components (`Filament\Forms\Components\TextInput`, `Filament\Tables\Columns\TextColumn`), not the parent `Forms`/`Tables` namespaces.
- For reactive `->options()` or `->live()` closures that receive `Get $get`, import `Filament\Schemas\Components\Utilities\Get` (NOT `Filament\Forms\Get` - that will throw a type error at runtime).
- Page classes set `protected static string $resource = {Name}Resource::class;`. List pages expose `CreateAction::make()` in `getHeaderActions()`. Create and Edit pages redirect with `getRedirectUrl(): string` returning `$this->getResource()::getUrl('index')`.
- Rich text uses `AmidEsfahani\FilamentTinyEditor\TinyEditor`.
- Select fields backed by an enum use `->options(SomeEnum::options())` and `->default(SomeEnum::CASE->value)`.
- Table text columns that can be long (headings, relation labels) use `->limit(30)->wrap()`.
- Enum-backed table columns render via `->getStateUsing(fn (ModelName $record): string => $record->field->label())` and `->color(fn (ModelName $record): string => $record->field->color())`. Always type the `$record` parameter and return type to satisfy 100% type coverage.
- Manage many-to-many pivots with a relationship multi-select: `Select::make('products')->relationship('products', 'heading')->multiple()->searchable()->preload()` (see `CouponResource`). No separate resource for pure scoping pivots.
- Manage a `hasMany` of line items inline on the parent's edit page with a Relation Manager in `app/Filament/Resources/{Parent}Resource/RelationManagers/{Children}RelationManager.php` (set `protected static string $relationship = 'childrenMethod';`, define `form()`/`table()` with `headerActions([CreateAction::make()])`), and register it in the parent's `getRelations()`. See `OrderResource` + `OrderVarietiesRelationManager` (an order has many `order_varieties`). The child can still have its own standalone resource for a global list.
- To filter relationship select options by another form field (reactive options): switch from `->relationship()` to `->options(fn (Get $get, ?Model $record): array => [...])`. Always include the current record's value in the options to prevent validation failures on edit: `if ($record?->field_id) { $ids = $ids->push($record->field_id)->unique(); }`.
- To reset a dependent field when its parent changes: add `->afterStateUpdated(fn (Set $set) => $set('dependent_field', null))` to the parent select alongside `->live()`.
- To show options immediately without typing, add `->preload()` to any `->multiple()` relationship select.
- `modifyQueryUsing` for relationship selects is the **3rd parameter** of `->relationship()`, not a chainable method: `->relationship('name', 'title', fn (Builder $q): Builder => $q->with('relation'))`. Calling `->modifyQueryUsing()` as a separate method throws `BadMethodCallException`.
- `->getOptionLabelFromRecordUsing(fn (Model $record): string => ...)` customises the label shown for each option in a relationship select. Pair with eager-loading in the `modifyQueryUsing` closure to avoid N+1.
- Control navigation order within a group with `protected static ?int $navigationSort = 1;` (lower = higher in the list).
- **A model's own `order` column must be paired with `->defaultSort('order')` on its table.** A sortable `order` column alone (e.g. `AncestorResource`, `AttributeGroupResource`) does nothing by default — the list still renders in insertion/id order every time it's opened, silently defeating the whole point of the field. See `FaqResource` for the reference pattern.
- **Never `withPivot()` a column that isn't actually migrated on the pivot table.** `AttributeGroup::categories()`/`Category::attributeGroups()` both declared a `order` pivot column that was never added to `attribute_group_category`, which threw `SQLSTATE[42703]: undefined column` the instant the relation was queried — verify pivot columns against the actual migration, not just intent.
- Add an explanatory subheading to a list page by overriding `mount()` on the `ListRecords` class: set `protected ?string $subheading = null;` and assign `$this->subheading = trans('resource.subheading');` inside `mount()`. Never use a hard-coded string — dynamic assignment is required for locale switching.
- Add a tooltip to a form field with `->hintIcon('heroicon-o-information-circle')->hintIconTooltip('Explanation...')`. Use this instead of always-visible `->hint()` when the text is long.
- Always add `->image()` to `FileUpload` fields that accept images. This restricts the file picker to image types only.
- **An image upload declares its shape through `App\Enums\ImageAspectEnum`, never a hardcoded ratio.** One case per slot, carrying the ratio the storefront actually renders that slot at, so the panel and the storefront cannot drift apart silently: `->imageEditor()->imageCropAspectRatio(ImageAspectEnum::CATEGORY->aspectRatio())->maxSize(ImageAspectEnum::CATEGORY->maxSizeKb())->helperText(ImageAspectEnum::CATEGORY->hint())`. A **`null` ratio is a decision, not a gap** — brand and gateway logos are drawn `object-contain`, so forcing a square would crop a wide wordmark, and `RECEIPT` (a customer's proof of payment) additionally gets **no `imageEditor()` at all**, because a crop can delete the reference number that is the point of the file. Banners and sliders are the one exception: their shape depends on the position stored on the record, so it lives on `BannerPositionEnum`/`SliderPositionEnum` instead (`aspectRatio()` + `recommendedSize()` there). `tests/Feature/Filament/ImageAspectTest.php` pins every ratio to the storefront component that renders it — update it in the same change. Shared hint strings are `system.image_hint` / `system.image_hint_free` in both `lang/fa` and `lang/en`.
- `mutateRelationshipDataBeforeSaveUsing` (and `BeforeCreateUsing`) MUST return `array`, never `null`. Returning `null` throws a `TypeError` at runtime. To skip saving, delete the related record inside the callback and still return the `$data` array.
- Self-referential FK (e.g. `parent_id`): use `$table->foreignId('parent_id')->nullable()->constrained('table_name')->nullOnDelete()`. In the factory, default `parent_id` to `null` and provide a named state (e.g. `withParent(Model $parent)`) to set it. In the `parent_id` select options closure, exclude the current record to prevent circular references: `->when($record?->id, fn (Builder $q) => $q->where('id', '!=', $record->id))`.
- If a model name clashes with a Filament concept (e.g. `Page`), alias the import in the resource file: `use App\Models\Page as PageModel`. This prevents naming ambiguity without renaming the model.
- Conditionally required fields: pair `->hidden()` and `->required()` with the same closure so the field is only required when visible. Example: `->required(fn (Get $get): bool => $get('status') === SomeEnum::CASE->value)->hidden(fn (Get $get): bool => $get('status') !== SomeEnum::CASE->value)`.
- Auto-generated slug fields use `->disabled()->dehydrated()` with `->unique(Model::class, 'slug', ignoreRecord: true)` to prevent duplicates while keeping the field read-only in the form.
- Secret fields (e.g. gateway `password`): cast `'password' => 'encrypted'` and add it to the model's `$hidden`. In the form use `->password()->revealable()->dehydrated(fn (?string $state): bool => filled($state))` so an empty input on edit keeps the stored value instead of wiping it. See `GatewayResource`.

## Localisation (fa / en)

- The admin panel supports Persian (`fa`) and English (`en`). The active locale is stored in the session and applied by `App\Http\Middleware\SetLocale` on every request.
- Admins switch locale via user-menu items ("English" / "فارسی") that hit `GET /locale/{locale}`.
- **Translation files**: one PHP file per resource at `lang/en/{resource}.php` and `lang/fa/{resource}.php`. Keys are flat strings (`label`, `plural_label`, `navigation_group`, `subheading`, field names, hint keys, enum values, …).
- Every resource **must** override `getNavigationGroup()`, `getModelLabel()`, and `getPluralModelLabel()` as methods returning `trans('{resource}.key')`. Do NOT use static properties for these.
- Resources with no navigation group (e.g. `UserResource`) still override `getModelLabel()` and `getPluralModelLabel()`.
- Every form field and table column **must** call `->label(trans('{resource}.key'))`. Never hard-code English labels.
- Use `->hintIconTooltip(trans('...'))` and `->helperText(trans('...'))` for hint and helper strings.
- Enum `label()` methods **must** call `trans()` (e.g. `trans('brand.status_active')`) so dropdown options and table badges switch language automatically.
- Subheadings on list pages use `mount()` (see Filament section above) — not a static string.
- Filament's own UI strings are covered by published vendor translations in `lang/vendor/filament*`.
- **Persian font**: `A Iranian Sans` is loaded via `public/fonts/AIranianSans.ttf` and `public/css/persian-font.css`. The `AdminPanelProvider` registers it with `->assets([Css::make('persian-font', asset('css/persian-font.css'))])` and applies it via a `renderHook` that injects an inline `<style>` block when `app()->getLocale() === 'fa'`.

## Models

- Declare `protected $fillable` (array) and `protected $casts` (array property, not the `casts()` method, except `User` which uses the `casts()` method).
- Cast enum columns to the enum class: `'status' => ProductStatusEnum::class`. Cast booleans to `'boolean'`.
- Add a class-level PHPDoc block listing every `@property` (including relations as `Collection<Model>` or `Model|null`).
- Type all relationship methods with their return type (`HasMany`, `BelongsTo`, `MorphOne`, `MorphMany`, `BelongsToMany`).
- Query scopes are typed: `public function scopePublished(Builder $query): Builder`.
- Model-event logic goes in `booted()` (see `Variety` syncing `Product.variety_counts` on `saved`/`deleted`) or a dedicated observer in `app/Observers/` registered from `AppServiceProvider::boot()`. Prefer an observer when the same hook must exist in both apps, or when the model already has a busy `booted()`.

## Caching (this panel is what invalidates the storefront's cache)

Read `docs/CACHE.md` before touching any of this.

- **Nothing in this panel reads the catalog cache; it only clears it.** So a broken invalidation hook is invisible from inside the panel — the only symptom is a customer being shown a price or stock count that staff already edited away. `tests/Feature/ProductCacheInvalidationTest.php` is the entire safety net, so extend it whenever a new write path appears.
- Invalidation goes through `App\Support\ProductCache` and the four observers (`Product`, `Variety`, `Image`, `Review`) registered by `AppServiceProvider::invalidateCatalogCache()`. Those five files are **mirrored byte-for-byte with `shop/`** — change both copies in the same commit, like the enums. `shop/tests/Feature/ProductCacheMirrorTest.php` fails the build on drift.
- It works only because both apps share one Redis store with pinned `CACHE_PREFIX`/`REDIS_PREFIX`. Never derive a cache prefix from `APP_NAME` — that's exactly how the two apps ended up in different namespaces while both were on redis, invalidating nothing with no error.
- **Hook `saved` *and* `updated`.** Filament's pivot-only edit (a `Select`/`Repeater` relationship synced after the record itself came out clean) fires only `saved`; `increment()`/`decrement()` — which is how `OrderObserver` moves stock — fires only `updated`.
- **A write that bypasses model events needs an explicit flush.** `truncate()` in a seeder, a mass query-builder `update()`, a `saveQuietly()`: none fire observers. `ProductSeeder`, `VarietySeeder` and `DemoSeeder` call `ProductCache::flushAll()` for exactly this reason (after the transaction commits, so a rolled-back seed hasn't thrown away a valid cache).
- Distinguish a change a product **card** shows (heading, slug, price, stock flag, status, category, brand, image) from one only the product page shows (content, SEO fields, `inventory`). The first flushes every listing; the second must not, or a busy shop's listings never stay warm.

## Enums

- Backed enums (usually `int`) in `app/Enums/`, e.g. `ProductStatusEnum: int` with explicit case values (`DELETED = 10`).
- Use the `App\Traits\HasOptions` trait to expose `options()` for Filament selects.
- Provide `label(): string` and, where shown in tables, `color(): string`, both using `match ($this)`.

## Migrations

- **Development rule**: NEVER create a new migration to add a column to a table that already has a migration in this branch. Update the existing `create_*` migration directly to keep history clean. After editing, run `php artisan migrate:fresh` inside the container to re-apply everything from scratch. Only create additive migrations when the table already exists in production.
- Anonymous class style: `return new class extends Migration`.
- Use `$table->foreignIdFor(Model::class)` for foreign keys (add `->nullable()` when optional). Chain `->constrained()->cascadeOnDelete()` / `->nullOnDelete()` / `->restrictOnDelete()` to add the real FK constraint with its delete rule.
- For a second FK to the same table, use a named column: `$table->foreignId('user_creator_id')->nullable()->constrained('users')->nullOnDelete()` (see `coupons`).
- Pivot tables add `$table->unique([...])` on the key pair and `->cascadeOnDelete()` on both FKs (see `coupon_product`).
- **Pivot table naming**: Laravel derives the name alphabetically from the two model names (singular). e.g. `Attribute` + `Variety` → `attribute_variety`, NOT `variety_attribute`. Always verify with this rule before writing migration or assertions.
- Default enum columns to a case value: `$table->unsignedTinyInteger('status')->default(ProductStatusEnum::PUBLISHED->value);`.
- When a column references a table that is not built yet (e.g. `accounting_id`), add it as a plain nullable column with no FK constraint, and add the real FK later when that table exists. See `orders.accounting_id`.
- Always implement `down()` with `Schema::dropIfExists(...)`.

## Factories

- `@extends Factory<Model>` PHPDoc, `definition(): array` with `@return array<string, mixed>`.
- Use the `fake()` helper (not `$this->faker`).
- Derive dependent fields with closures: `'slug' => fn (array $attributes): string => Str::slug($attributes['heading'])`.
- Random enum values via `fake()->randomElement(SomeEnum::cases())`.
- Counts/relations that are computed at runtime (like `variety_counts`) default to `0`, not random.
- Optional related data goes in named states using `afterCreating()` (e.g. `withImage()`, `withImages()`, `withAttributes()`).
- For unique slug fields, use `Str::uuid()` not `fake()->unique()->numberBetween()`. The `unique()` state accumulates across all tests in a suite and can cause cross-test collisions.

## Seeders

- `DatabaseSeeder::run()` calls all seeders via `$this->call([...])` in dependency order. It holds only necessary/reference data (roles, admin, cities, categories, ancestors, attributes, etc.).
- `TestSeeder` holds factory-generated sample data (`Model::factory()->count(20)->create()`) for manual admin-panel testing. Run it separately with `php artisan db:seed --class=TestSeeder`. Add new sample-data seeders here, not in `DatabaseSeeder`.
- Reference seeders use idempotent `updateOrCreate()` / `firstOrCreate()` so re-seeding is safe.
- When truncating and re-seeding a table whose model has a `deleting` event (e.g. to cascade-delete related images), delete records one by one via `Model::all()->each->delete()` BEFORE truncating the parent. Use `->each->delete()` on a **Collection**, not a query builder — `Model::query()->each` does not exist and will throw an exception.
- **Don't `truncate()` a table that is referenced by a foreign key.** Postgres refuses to `TRUNCATE` a table another table FK-references (e.g. `slides.slider_id → sliders`) even after the referencing rows are deleted, unless you `CASCADE`. Use `Model::query()->delete()` instead (after clearing the children). `SliderSeeder` hit this — it deletes slides via `->each->delete()` (fires their image cleanup) then `Slider::query()->delete()`.
- Seed data should honor code-level enum constraints and how the storefront reads them: `SliderSeeder` creates exactly one PUBLISHED slider per `SliderPositionEnum` case (not N random-position rows), because the storefront shows the *first published slider per position* — random duplicates would make which one appears nondeterministic.
- **A "delete-then-recreate" seeder must clear every table that FK-references it first, not just its own model's `deleting` event.** `ShippingLineSeeder` deleting all `shipping_lines` threw `SQLSTATE[23503]` because `shipping_methods`/`shipping_cities` (no cascade) still referenced them; `CitySeeder` deleting `cities` hit the same thing via `addresses.city_id` (worse: `Address` uses `SoftDeletes`, so even a soft-deleted row still blocks the FK — use `withTrashed()->forceDelete()`, not a plain `delete()`, to actually clear it). Check every migration for FKs into the table you're about to wipe, not just the ones you already know about.
- **`TestSeeder` must never seed a table that a "real" seeder (`DatabaseSeeder`'s chain) already populates with load-bearing data.** `TestSeeder` used to include `ShippingLineSeeder`/`ShippingMethodSeeder`/`ShippingCitySeeder` (20 random rows each); since `DatabaseSeeder` → `ShippingSeeder` already seeds the 3 real, checkout-critical shipping methods, running `TestSeeder` afterward silently replaced them with random fake ones tied to random specific cities (no nationwide fallback) — breaking the storefront checkout's shipping-method selection with no visible error until a customer tried to check out. Removed from `TestSeeder` entirely; re-run `ShippingSeeder` if this ever regresses.
- Read configurable values from config, not literals (see `AdminSeeder` reading `config('admin.account')`).

## Pest tests

- Feature tests live in `tests/Feature/Filament/Resource/{Name}ResourceTest.php` and use `RefreshDatabase` (configured in `tests/Pest.php`).
- Call the global `login()` helper in `beforeEach()` to authenticate a super-admin.
- Describe tests with `it('can ... .', function () { ... })`.
- Use the `livewire()` helper for Filament pages: `->fillForm([...])->call('create'|'save')->assertHasNoFormErrors()`, and `->callAction(DeleteAction::class)` for deletes.
- Build expected data with `Model::factory()->make()` (unsaved) and seed existing rows with `->create()`.
- Assert with `assertDatabaseHas(...)`, `assertModelMissing(...)`, or `expect($model->refresh())->field->toBe(...)`.
- Do not assert computed fields against a fresh factory value; assert the expected computed result.

## Business constraints

- **No seller / marketplace system.** ShopFlow is a single-vendor store. There are no sellers, seller accounts, or seller-scoped entities. Never add `seller_id`, `seller_creator_id`, or any seller relation to models, migrations, or resources.

## Roadmap & docs

- **Before starting any task**, read these three files to understand the current state of the project:
  - `IMPLEMENTATION.md` — what is done, what is next, and the priority order.
  - `ShoFlow db doc.md` — the full schema reference; treat it as the source of truth for table columns and relationships.
  - `CACHE.md` — cache keys that have been identified but not yet implemented.
  - `ORDER.md` — the orders/inventory rules: stock is decremented only on successful payment (Strategy A), carts never change inventory.
- When an entity is finished or the plan changes, update `IMPLEMENTATION.md`.
- When adding a model whose data is likely to be cached (products, categories, banners, menus, etc.), check `CACHE.md` and add or update the relevant rows.
- Keep this "ShopFlow Admin Conventions" section updated whenever a new reusable pattern is introduced.

