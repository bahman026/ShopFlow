<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12

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
- **Closure parameters must also be typed** (e.g. `fn (mixed $id): int =>` not `fn ($id): int =>`). Untyped closure parameters drop type coverage below 100% and fail `composer test-dev`.
- **Nullsafe operator**: only use `?->` on relations whose PHPDoc type is nullable. If the PHPDoc says `@property Product $product` (non-nullable), use `->` — PHPStan will flag `?->` as `nullsafe.neverNull`.
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

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

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

# ShopFlow Admin Conventions

Project-specific patterns. Match these when adding or editing code. All PHP files use `declare(strict_types=1);` and are formatted by Pint (`vendor/bin/pint`).

## Running commands and tests

- The app runs in Docker. Execute commands inside the container: `docker exec -it -u www-data shop_flow_admin_app bash`.
- Before committing, run `composer test-dev` (Pest, Pint, type coverage, PHPStan) inside the container and make sure it passes.
- Commit with this author: `Bahman026 <bahman026@gmail.com>` (use `git commit --author="Bahman026 <bahman026@gmail.com>"`).
- Always ask before committing. NEVER commit without explicit user approval.

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
  - `protected static string | \UnitEnum | null $navigationGroup = 'Product';`
  - `protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';`
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
- To filter relationship select options by another form field (reactive options): switch from `->relationship()` to `->options(fn (Get $get, ?Model $record): array => [...])`. Always include the current record's value in the options to prevent validation failures on edit: `if ($record?->field_id) { $ids = $ids->push($record->field_id)->unique(); }`.
- To reset a dependent field when its parent changes: add `->afterStateUpdated(fn (Set $set) => $set('dependent_field', null))` to the parent select alongside `->live()`.
- To show options immediately without typing, add `->preload()` to any `->multiple()` relationship select.
- `modifyQueryUsing` for relationship selects is the **3rd parameter** of `->relationship()`, not a chainable method: `->relationship('name', 'title', fn (Builder $q): Builder => $q->with('relation'))`. Calling `->modifyQueryUsing()` as a separate method throws `BadMethodCallException`.
- `->getOptionLabelFromRecordUsing(fn (Model $record): string => ...)` customises the label shown for each option in a relationship select. Pair with eager-loading in the `modifyQueryUsing` closure to avoid N+1.
- Control navigation order within a group with `protected static ?int $navigationSort = 1;` (lower = higher in the list).
- Add an explanatory subheading to a list page with `protected ?string $subheading = 'Description here.';` on the `ListRecords` page class.
- Add a tooltip to a form field with `->hintIcon('heroicon-o-information-circle')->hintIconTooltip('Explanation...')`. Use this instead of always-visible `->hint()` when the text is long.
- Always add `->image()` to `FileUpload` fields that accept images. This restricts the file picker to image types only.
- `mutateRelationshipDataBeforeSaveUsing` (and `BeforeCreateUsing`) MUST return `array`, never `null`. Returning `null` throws a `TypeError` at runtime. To skip saving, delete the related record inside the callback and still return the `$data` array.
- Self-referential FK (e.g. `parent_id`): use `$table->foreignId('parent_id')->nullable()->constrained('table_name')->nullOnDelete()`. In the factory, default `parent_id` to `null` and provide a named state (e.g. `withParent(Model $parent)`) to set it. In the `parent_id` select options closure, exclude the current record to prevent circular references: `->when($record?->id, fn (Builder $q) => $q->where('id', '!=', $record->id))`.
- If a model name clashes with a Filament concept (e.g. `Page`), alias the import in the resource file: `use App\Models\Page as PageModel`. This prevents naming ambiguity without renaming the model.
- Conditionally required fields: pair `->hidden()` and `->required()` with the same closure so the field is only required when visible. Example: `->required(fn (Get $get): bool => $get('status') === SomeEnum::CASE->value)->hidden(fn (Get $get): bool => $get('status') !== SomeEnum::CASE->value)`.
- Auto-generated slug fields use `->disabled()->dehydrated()` with `->unique(Model::class, 'slug', ignoreRecord: true)` to prevent duplicates while keeping the field read-only in the form.

## Models

- Declare `protected $fillable` (array) and `protected $casts` (array property, not the `casts()` method, except `User` which uses the `casts()` method).
- Cast enum columns to the enum class: `'status' => ProductStatusEnum::class`. Cast booleans to `'boolean'`.
- Add a class-level PHPDoc block listing every `@property` (including relations as `Collection<Model>` or `Model|null`).
- Type all relationship methods with their return type (`HasMany`, `BelongsTo`, `MorphOne`, `MorphMany`, `BelongsToMany`).
- Query scopes are typed: `public function scopePublished(Builder $query): Builder`.
- Model-event logic goes in `booted()` (see `Variety` syncing `Product.variety_counts` on `saved`/`deleted`).

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
- Read configurable values from config, not literals (see `AdminSeeder` reading `config('admin.account')`).

## Pest tests

- Feature tests live in `tests/Feature/Filament/Resource/{Name}ResourceTest.php` and use `RefreshDatabase` (configured in `tests/Pest.php`).
- Call the global `login()` helper in `beforeEach()` to authenticate a super-admin.
- Describe tests with `it('can ... .', function () { ... })`.
- Use the `livewire()` helper for Filament pages: `->fillForm([...])->call('create'|'save')->assertHasNoFormErrors()`, and `->callAction(DeleteAction::class)` for deletes.
- Build expected data with `Model::factory()->make()` (unsaved) and seed existing rows with `->create()`.
- Assert with `assertDatabaseHas(...)`, `assertModelMissing(...)`, or `expect($model->refresh())->field->toBe(...)`.
- Do not assert computed fields against a fresh factory value; assert the expected computed result.

## Roadmap & docs

- **Before starting any task**, read these three files to understand the current state of the project:
  - `IMPLEMENTATION.md` — what is done, what is next, and the priority order.
  - `ShoFlow db doc.md` — the full schema reference; treat it as the source of truth for table columns and relationships.
  - `CACHE.md` — cache keys that have been identified but not yet implemented.
- When an entity is finished or the plan changes, update `IMPLEMENTATION.md`.
- When adding a model whose data is likely to be cached (products, categories, banners, menus, etc.), check `CACHE.md` and add or update the relevant rows.
- Keep this "ShopFlow Admin Conventions" section updated whenever a new reusable pattern is introduced.

