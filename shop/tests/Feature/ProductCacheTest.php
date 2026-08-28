<?php

declare(strict_types=1);

use App\Enums\BrandStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\ReviewStatusEnum;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Review;
use App\Models\Variety;
use App\Support\ProductCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

/**
 * The storefront half of the catalog cache: that pages really are served from
 * cache, and that every write which changes what a customer sees drops the
 * entry again. The admin half — staff edits reaching these same keys — is
 * covered by `admin/tests/Feature/ProductCacheInvalidationTest.php`.
 *
 * A note on asserting list invalidation. List keys carry a generation token, so
 * "invalidated" means *unreachable*, not deleted: the old entry can still sit in
 * the store until its TTL. Every assertion below therefore recomputes the key
 * through `ProductCache` instead of reusing a variable captured earlier — the
 * captured string would still resolve and the test would pass vacuously.
 */
function cacheProduct(string $categorySlug = 'cache-cat'): Product
{
    return catProduct(catCategory($categorySlug));
}

/**
 * @return array<int, string>|null
 */
function warmList(string $scope): array
{
    Cache::put(ProductCache::listKey('probe', ['scope' => $scope]), ['warm'], 600);

    return ['warm'];
}

function probedList(string $scope): mixed
{
    return Cache::get(ProductCache::listKey('probe', ['scope' => $scope]));
}

it('serves the product page from cache on the second visit', function (): void {
    $product = cacheProduct();

    $this->get('/products/'.$product->slug)->assertOk();

    $key = ProductCache::detailKey($product->slug);
    expect(Cache::has($key))->toBeTrue();

    // Prove the next render *reads* the entry rather than rebuilding it: a
    // sentinel heading that exists nowhere in the database can only come from
    // the cache.
    $entry = Cache::get($key);
    $entry['product']['heading'] = 'از کش خوانده شد';
    Cache::put($key, $entry, 600);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('product.heading', 'از کش خوانده شد')
        );
});

it('still counts a view on a cache hit', function (): void {
    $product = cacheProduct();

    $this->get('/products/'.$product->slug)->assertOk();
    $this->get('/products/'.$product->slug)->assertOk();
    $this->get('/products/'.$product->slug)->assertOk();

    expect($product->refresh()->seen)->toBe(3);
});

it('keeps the cached page across visits even though each view bumps seen', function (): void {
    // The regression this guards: `products.seen` is written on every product
    // page view, so an observer treating it as a content change would delete
    // the entry on every visit and nothing would ever be served warm — the
    // cache would look implemented and do nothing.
    $product = cacheProduct();

    $this->get('/products/'.$product->slug)->assertOk();

    $key = ProductCache::detailKey($product->slug);
    $entry = Cache::get($key);
    $entry['product']['heading'] = 'باید بماند';
    Cache::put($key, $entry, 600);

    $this->get('/products/'.$product->slug)->assertOk();
    $this->get('/products/'.$product->slug)->assertOk();

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('product.heading', 'باید بماند')
        );
});

it('drops the cached page when a variety price changes', function (): void {
    $product = cacheProduct();
    $this->get('/products/'.$product->slug)->assertOk();

    Variety::query()->where('product_id', $product->id)->firstOrFail()->update(['price' => 777000]);

    expect(Cache::has(ProductCache::detailKey($product->slug)))->toBeFalse();
});

it('drops the cached page when inventory is decremented, and leaves the lists warm', function (): void {
    // Strategy A decrements stock on a paid order (`DecrementInventoryAndMarkPaid`)
    // with decrement(), which fires `updated` but never `saved` — an observer
    // hooking only `saved` would miss every single sale.
    $product = cacheProduct();
    $this->get('/products/'.$product->slug)->assertOk();
    warmList('inventory');

    Variety::query()->where('product_id', $product->id)->firstOrFail()->decrement('inventory');

    expect(Cache::has(ProductCache::detailKey($product->slug)))->toBeFalse()
        // Inventory shows on a product page and on no card, so a sale must not
        // cost every category listing in the shop its cache.
        ->and(probedList('inventory'))->toBe(['warm']);
});

it('drops every cached list when a variety price changes', function (): void {
    $product = cacheProduct();
    warmList('price');

    Variety::query()->where('product_id', $product->id)->firstOrFail()->update(['price' => 5]);

    expect(probedList('price'))->toBeNull();
});

it('drops the cached page when a review is approved', function (): void {
    $product = cacheProduct();
    $this->get('/products/'.$product->slug)->assertOk();

    Review::create([
        'heading' => 'خوب بود',
        'content' => 'راضی بودم',
        'rating' => 5,
        'product_id' => $product->id,
        'status' => ReviewStatusEnum::APPROVED,
    ]);

    expect(Cache::has(ProductCache::detailKey($product->slug)))->toBeFalse();
});

it('drops the cached page for the old slug when a product is renamed', function (): void {
    $product = cacheProduct();
    $oldSlug = $product->slug;

    $this->get('/products/'.$oldSlug)->assertOk();

    $product->update(['slug' => 'renamed-'.uniqid()]);

    expect(Cache::has(ProductCache::detailKey($oldSlug)))->toBeFalse();
});

it('drops every cached list when a product is unpublished', function (): void {
    $product = cacheProduct();
    warmList('status');

    $product->update(['status' => ProductStatusEnum::DRAFT]);

    expect(probedList('status'))->toBeNull();
});

it('leaves the lists warm when only a product description changes', function (): void {
    $product = cacheProduct();
    $this->get('/products/'.$product->slug)->assertOk();
    warmList('content');

    $product->update(['content' => '<p>متن تازه</p>']);

    expect(Cache::has(ProductCache::detailKey($product->slug)))->toBeFalse()
        ->and(probedList('content'))->toBe(['warm']);
});

it('caches the category listing, and keys a different sort separately', function (): void {
    $category = catCategory('listing-cat');
    catProduct($category, 100000);
    catProduct($category, 200000);

    $this->get('/categories/'.$category->slug)->assertOk();

    $signature = fn (string $sort): array => [
        'categories' => [$category->id],
        'filters' => ['brands' => [], 'attributes' => [], 'minPrice' => null, 'maxPrice' => null, 'inStock' => false, 'sort' => $sort],
        'page' => 1,
    ];

    expect(Cache::has(ProductCache::listKey('category', $signature('newest'))))->toBeTrue()
        ->and(Cache::has(ProductCache::listKey('category', $signature('cheapest'))))->toBeFalse();
});

it('serves page 2 of a listing its own payload, not page 1', function (): void {
    // `paginate()` reads the page from the request rather than from the
    // action's arguments, so the page number has to be part of the cache
    // signature explicitly — without it page 2 would replay page 1.
    $category = catCategory('paged-cat');

    foreach (range(1, 26) as $ignored) {
        catProduct($category);
    }

    $this->get('/categories/'.$category->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.currentPage', 1)
            ->has('products.data', 24)
        );

    $this->get('/categories/'.$category->slug.'?page=2')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.currentPage', 2)
            ->has('products.data', 2)
        );
});

it('fingerprints equivalent filters to the same list key whatever their order', function (): void {
    expect(ProductCache::listKey('category', ['filters' => ['brands' => ['nike', 'adidas']]]))
        ->toBe(ProductCache::listKey('category', ['filters' => ['brands' => ['adidas', 'nike']]]));
});

/*
|--------------------------------------------------------------------------
| Catalog metadata — category, brand, attribute, attribute group
|--------------------------------------------------------------------------
|
| A product page renders all of these live off their relations, so a rename has
| to reach the cached payload. They flush the generation rather than deleting the
| entry, so every assertion recomputes the key through `ProductCache`.
*/

/**
 * An attribute group, built through the query builder because `ancestor_id` is
 * required and not fillable on the storefront's read-only model.
 */
function metaGroup(string $name = 'رنگ'): AttributeGroup
{
    $ancestorId = DB::table('ancestors')->insertGetId([
        'name' => $name, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $groupId = DB::table('attribute_groups')->insertGetId([
        'ancestor_id' => $ancestorId, 'name' => $name, 'created_at' => now(), 'updated_at' => now(),
    ]);

    return AttributeGroup::findOrFail($groupId);
}

function cachedDetail(Product $product): mixed
{
    return Cache::get(ProductCache::detailKey($product->slug));
}

it('shows the new category name on the product page after a rename', function (): void {
    // The end-to-end version of this whole feature: without a CategoryObserver
    // the second request replays the cached payload and the customer keeps
    // reading the old category name for the rest of the entry's TTL.
    $category = catCategory('rename-cat');
    $product = catProduct($category);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('product.category.heading', 'دسته rename-cat')
        );

    $category->update(['heading' => 'دستهٔ تازه']);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('product.category.heading', 'دستهٔ تازه')
        );
});

it('drops the cached product page when its category is renamed', function (): void {
    $category = catCategory('meta-cat');
    $product = catProduct($category);
    $this->get('/products/'.$product->slug)->assertOk();

    $category->update(['heading' => 'دسته تازه']);

    expect(cachedDetail($product))->toBeNull();
});

it('drops the cached product page when an ancestor category is renamed', function (): void {
    // Breadcrumbs walk parent_id upwards, so a product two levels down renders
    // the name of a category it does not itself belong to.
    $root = catCategory('root-cat');
    $leaf = catCategory('leaf-cat', parentId: $root->id);
    $product = catProduct($leaf);
    $this->get('/products/'.$product->slug)->assertOk();

    $root->update(['heading' => 'ریشهٔ تازه']);

    expect(cachedDetail($product))->toBeNull();
});

it('keeps the cached product page when only category SEO copy changes', function (): void {
    $category = catCategory('seo-cat');
    $product = catProduct($category);
    $this->get('/products/'.$product->slug)->assertOk();

    $category->update(['content' => '<p>متن سئو</p>', 'no_index' => true]);

    expect(cachedDetail($product))->not->toBeNull();
});

it('drops the cached product page when its brand is renamed', function (): void {
    $brand = Brand::create(['heading' => 'نایک', 'slug' => 'nike-meta', 'status' => BrandStatusEnum::ACTIVE]);
    $product = catProduct(catCategory('brand-cat'), brandId: $brand->id);
    $this->get('/products/'.$product->slug)->assertOk();

    $brand->update(['heading' => 'نایکی']);

    expect(cachedDetail($product))->toBeNull();
});

it('drops the cached product page when a descriptive attribute is renamed', function (): void {
    // Rendered in the spec table via the product_attribute pivot.
    $attribute = Attribute::create(['attribute_group_id' => metaGroup()->id, 'value' => 'قرمز', 'color' => '#ff0000']);
    $product = catProduct(catCategory('attr-cat'), attributeId: $attribute->id);
    $this->get('/products/'.$product->slug)->assertOk();

    $attribute->update(['value' => 'زرشکی']);

    expect(cachedDetail($product))->toBeNull();
});

it('shows the new variety label and axis option after an attribute rename', function (): void {
    // Both halves of the page have to agree. The axis option is read live off
    // the attribute relation; the variety's own label comes from the
    // denormalised `attribute_value` column, which `AttributeObserver` resyncs.
    // Before that resync existed, this page rendered the new name on the button
    // and the old one on the variety — cache or no cache.
    $attribute = Attribute::create(['attribute_group_id' => metaGroup()->id, 'value' => 'قرمز', 'color' => '#ff0000']);
    $product = catProduct(catCategory('label-cat'));

    // A variety already in sync, exactly as admin's Variety::booted() leaves it.
    // Updated through the query builder so no observer fires before warming.
    Variety::query()->where('product_id', $product->id)->update([
        'attribute_id' => $attribute->id,
        'attribute_value' => 'قرمز',
        'color' => '#ff0000',
    ]);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('product.varieties.0.label', 'قرمز')
        );

    $attribute->update(['value' => 'زرشکی']);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('product.varieties.0.label', 'زرشکی')
            ->where('product.variantAxes.0.options.0.value', 'زرشکی')
        );
});

it('drops the cached product page when an attribute group is renamed', function (): void {
    // The group name is the heading of the spec row its attributes sit under.
    $group = metaGroup();
    $attribute = Attribute::create(['attribute_group_id' => $group->id, 'value' => 'قرمز']);
    $product = catProduct(catCategory('group-cat'), attributeId: $attribute->id);
    $this->get('/products/'.$product->slug)->assertOk();

    $group->update(['name' => 'اندازه']);

    expect(cachedDetail($product))->toBeNull();
});

it('makes both product pages and lists unreachable on flushAll', function (): void {
    // The escape hatch for writes no observer sees: a seeder truncate, a mass
    // query-builder update, a manual SQL fix.
    $product = cacheProduct();
    $this->get('/products/'.$product->slug)->assertOk();
    warmList('flush');

    expect(Cache::has(ProductCache::detailKey($product->slug)))->toBeTrue();

    ProductCache::flushAll();

    expect(Cache::has(ProductCache::detailKey($product->slug)))->toBeFalse()
        ->and(probedList('flush'))->toBeNull();
});
