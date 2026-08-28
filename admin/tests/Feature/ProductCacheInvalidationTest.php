<?php

declare(strict_types=1);

use App\Enums\OrderStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\ReviewStatusEnum;
use App\Filament\Resources\ProductResource;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\Product;
use App\Models\Review;
use App\Models\Tag;
use App\Models\Variety;
use App\Support\ProductCache;
use Illuminate\Support\Facades\Cache;

use function Pest\Livewire\livewire;

/**
 * The panel side of the catalog cache: staff edits must clear the entries the
 * *storefront* wrote. Nothing in this app ever reads them, so a broken hook here
 * is invisible from inside the panel — the only symptom is a customer being
 * shown a price or a stock count that was edited away, until the entry's TTL
 * runs out. Hence testing invalidation directly rather than through a read.
 *
 * The storefront half (that pages are genuinely served from cache, and that a
 * paid order clears them) is covered by `shop/tests/Feature/ProductCacheTest.php`.
 *
 * These tests use the `array` store, so they prove the *hooks* fire and address
 * the right keys. That the two apps' keys actually collide in one Redis
 * namespace is a matter of configuration, pinned in `config/cache.php` and
 * `config/database.php` and asserted by `CatalogCacheStoreTest`.
 */
beforeEach(function () {
    login();
});

/**
 * A product with one variety, and its storefront page entry primed exactly as
 * the storefront would have left it.
 *
 * @return array{0: Product, 1: Variety, 2: string}
 */
function primedProduct(): array
{
    $product = Product::factory()->create(['status' => ProductStatusEnum::PUBLISHED]);
    $variety = Variety::factory()->create(['product_id' => $product->id, 'inventory' => 10]);

    $key = ProductCache::detailKey($product->slug);
    Cache::put($key, ['product' => ['heading' => 'کش قدیمی']], 600);

    return [$product, $variety, $key];
}

/**
 * A product's page entry primed, plus a reader that **recomputes** its key.
 *
 * Needed for the catalog-metadata observers below, which flush the detail
 * generation rather than deleting the entry: the old key string still resolves
 * to the orphaned entry, so asserting on a captured key would pass vacuously
 * (or fail for the wrong reason). Only a rebuilt key proves reachability.
 *
 * @return array{0: Product, 1: Closure(): mixed}
 */
function primedDetail(): array
{
    $product = Product::factory()->create(['status' => ProductStatusEnum::PUBLISHED]);
    Variety::factory()->create(['product_id' => $product->id, 'inventory' => 10]);

    Cache::put(ProductCache::detailKey($product->slug), ['product' => ['heading' => 'کش قدیمی']], 600);

    return [$product, fn (): mixed => Cache::get(ProductCache::detailKey($product->slug))];
}

/**
 * A list entry primed under the current generation, plus a reader that
 * recomputes the key. List invalidation moves the generation rather than
 * deleting entries, so the key has to be rebuilt to test reachability — a
 * captured string would still resolve and pass vacuously.
 */
function primedList(string $scope): Closure
{
    Cache::put(ProductCache::listKey('probe', ['scope' => $scope]), ['warm'], 600);

    return fn (): mixed => Cache::get(ProductCache::listKey('probe', ['scope' => $scope]));
}

it('clears a product page when staff save the product in the panel', function () {
    [$product, , $key] = primedProduct();

    livewire(ProductResource\Pages\EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['heading' => 'نام تازه'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Cache::has($key))->toBeFalse();
});

it('clears the old slug too when staff rename a product', function () {
    [$product, , $oldKey] = primedProduct();

    $product->update(['slug' => 'a-new-slug']);

    expect(Cache::has($oldKey))->toBeFalse()
        ->and(Cache::has(ProductCache::detailKey('a-new-slug')))->toBeFalse();
});

it('clears a product page when staff change a variety price', function () {
    [, $variety, $key] = primedProduct();
    $list = primedList('variety-price');

    $variety->update(['price' => 99000]);

    expect(Cache::has($key))->toBeFalse()
        // A card shows the cheapest variety price, so the listings go too.
        ->and($list())->toBeNull();
});

it('clears a product page when staff change inventory, leaving the listings warm', function () {
    [, $variety, $key] = primedProduct();
    $list = primedList('variety-inventory');

    $variety->update(['inventory' => 2]);

    expect(Cache::has($key))->toBeFalse()
        // Inventory reaches a product page and no card. Flushing every listing
        // on each stock movement would keep them permanently cold on a busy
        // shop, which is the whole reason detail and list entries carry
        // separate generations.
        ->and($list())->toBe(['warm']);
});

it('clears a product page when a variety is deleted', function () {
    [, $variety, $key] = primedProduct();

    $variety->delete();

    expect(Cache::has($key))->toBeFalse();
});

it('clears a product page when an order status change moves stock', function () {
    // `OrderObserver` decrements inventory with decrement(), which fires
    // `updated` but never `saved`. This is the panel's equivalent of the
    // storefront's paid-order decrement.
    [$product, $variety, $key] = primedProduct();

    $order = Order::factory()->create(['status' => OrderStatusEnum::PENDING]);
    OrderVariety::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variety_id' => $variety->id,
        'quantity' => 3,
        'price' => 1000,
        'final_price' => 1000,
    ]);

    $order->refresh()->update(['status' => OrderStatusEnum::PAID]);

    expect($variety->fresh()->inventory)->toBe(7)
        ->and(Cache::has($key))->toBeFalse();
});

it('clears a product page when staff approve a review', function () {
    [$product, , $key] = primedProduct();

    $review = Review::factory()->create([
        'product_id' => $product->id,
        'status' => ReviewStatusEnum::PENDING,
    ]);

    Cache::put($key, ['product' => ['heading' => 'کش قدیمی']], 600);

    $review->update(['status' => ReviewStatusEnum::APPROVED]);

    expect(Cache::has($key))->toBeFalse();
});

it('clears a product page and the listings when a product image changes', function () {
    [$product, , $key] = primedProduct();
    $list = primedList('product-image');

    Image::create([
        'path' => 'media/new.jpg',
        'imageable_type' => Product::class,
        'imageable_id' => $product->id,
        'is_featured' => true,
    ]);

    expect(Cache::has($key))->toBeFalse()
        ->and($list())->toBeNull();
});

it('clears a product page and the listings when a variety image changes', function () {
    // A card falls back to the first variety image when the product has none,
    // so a variety photo can be what a whole category listing renders.
    [, $variety, $key] = primedProduct();
    $list = primedList('variety-image');

    Image::create([
        'path' => 'media/variety.jpg',
        'imageable_type' => Variety::class,
        'imageable_id' => $variety->id,
        'is_featured' => true,
    ]);

    expect(Cache::has($key))->toBeFalse()
        ->and($list())->toBeNull();
});

it('leaves the catalog cache alone for an image outside the catalog', function () {
    // `images` is polymorphic and shared with banners, brands and menu items —
    // those must not flush the product listings.
    [, , $key] = primedProduct();
    $list = primedList('foreign-image');

    Cache::put($key, ['product' => ['heading' => 'کش قدیمی']], 600);

    Image::create([
        'path' => 'media/banner.jpg',
        'imageable_type' => 'App\Models\Banner',
        'imageable_id' => 999999,
        'is_featured' => false,
    ]);

    expect(Cache::has($key))->toBeTrue()
        ->and($list())->toBe(['warm']);
});

it('clears a product page and the listings when a product is deleted', function () {
    [$product, , $key] = primedProduct();
    $list = primedList('product-delete');

    $product->delete();

    expect(Cache::has($key))->toBeFalse()
        ->and($list())->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Catalog metadata — category, brand, attribute, attribute group
|--------------------------------------------------------------------------
|
| These flush both generations rather than forgetting one page, because the
| products affected are spread across a category's whole descendant subtree
| (breadcrumbs) or three attribute pivots at once. A flushed detail generation
| means `detailKey()` returns a *new* key, so "invalidated" is asserted by
| recomputing the key and finding nothing there — exactly as for the lists.
*/

it('clears the catalog cache when staff rename a category', function () {
    // BuildProductDetail renders the category link live, and the breadcrumb
    // trail walks parent_id upwards, so a rename reaches every product in the
    // category and every category beneath it.
    $category = Category::factory()->create();
    [, $detail] = primedDetail();
    $list = primedList('category-rename');

    $category->update(['heading' => 'نام تازه دسته']);

    expect($detail())->toBeNull()
        ->and($list())->toBeNull();
});

it('clears the catalog cache when a category is re-parented', function () {
    // Re-parenting rewrites the breadcrumb trail of every product underneath it.
    $parent = Category::factory()->create();
    $child = Category::factory()->create();
    [, $detail] = primedDetail();

    $child->update(['parent_id' => $parent->id]);

    expect($detail())->toBeNull();
});

it('keeps the catalog cache when only category SEO copy changes', function () {
    // content/title/description/no_index/canonical render on the category's own
    // page, which is not cached. Editing them must not cool the whole catalog.
    $category = Category::factory()->create();
    [, $detail] = primedDetail();
    $list = primedList('category-seo');

    $category->update(['content' => '<p>متن سئو</p>', 'title' => 'عنوان', 'no_index' => true]);

    expect($detail())->not->toBeNull()
        ->and($list())->toBe(['warm']);
});

it('clears the catalog cache when staff rename a brand', function () {
    $brand = Brand::factory()->create();
    [, $detail] = primedDetail();
    $list = primedList('brand-rename');

    $brand->update(['heading' => 'برند تازه']);

    expect($detail())->toBeNull()
        ->and($list())->toBeNull();
});

it('clears the catalog cache when a brand is deleted', function () {
    // The sharp case: products.brand_id is nullOnDelete, so the database
    // rewrites every one of the brand's products with no Product event firing.
    // Nothing but this flush stands between a deleted brand and cached pages
    // still advertising it.
    $brand = Brand::factory()->create();
    [, $detail] = primedDetail();
    $list = primedList('brand-delete');

    $brand->delete();

    expect($detail())->toBeNull()
        ->and($list())->toBeNull();
});

it('clears the catalog cache when staff rename an attribute', function () {
    // Read live in three places: the spec table (product_attribute), the primary
    // variant axis (varieties.attribute_id) and the secondary axes
    // (attribute_variety).
    $attribute = Attribute::factory()->create();
    [, $detail] = primedDetail();
    $list = primedList('attribute-rename');

    $attribute->update(['value' => 'زرشکی']);

    expect($detail())->toBeNull()
        ->and($list())->toBeNull();
});

it('clears the catalog cache when an attribute colour changes', function () {
    // Rendered as the swatch on a colour variant axis.
    $attribute = Attribute::factory()->create(['color' => '#ff0000']);
    [, $detail] = primedDetail();

    $attribute->update(['color' => '#00ff00']);

    expect($detail())->toBeNull();
});

it('clears the catalog cache when staff rename an attribute group', function () {
    // The group name is the spec row heading and the variant axis label.
    $group = AttributeGroup::factory()->create();
    [, $detail] = primedDetail();
    $list = primedList('group-rename');

    $group->update(['name' => 'اندازه']);

    expect($detail())->toBeNull()
        ->and($list())->toBeNull();
});

it('keeps the catalog cache when only an attribute group label changes', function () {
    // `label` is documented as admin-panel-only and is never rendered to a
    // customer (GetCategoryFilters displays `name`), so it must not flush.
    $group = AttributeGroup::factory()->create();
    [, $detail] = primedDetail();
    $list = primedList('group-label');

    $group->update(['label' => 'برچسب داخلی تازه']);

    expect($detail())->not->toBeNull()
        ->and($list())->toBe(['warm']);
});

it('clears the catalog cache when an attribute group is reordered', function () {
    // `order` sequences the facet groups on a category page.
    $group = AttributeGroup::factory()->create(['order' => 1]);
    [, $detail] = primedDetail();

    $group->update(['order' => 9]);

    expect($detail())->toBeNull();
});

it('keeps the catalog cache when catalog metadata is merely created', function () {
    // A brand-new category/brand/attribute is attached to no product, so nothing
    // cached can be showing it. Without the wasRecentlyCreated guard every
    // seeder run would flush the catalog once per row — Eloquent reports every
    // attribute as changed on an insert.
    [, $detail] = primedDetail();
    $list = primedList('metadata-create');

    Category::factory()->create();
    Brand::factory()->create();
    Attribute::factory()->create();
    AttributeGroup::factory()->create();

    expect($detail())->not->toBeNull()
        ->and($list())->toBe(['warm']);
});

/*
|--------------------------------------------------------------------------
| Tags — the home page's featured carousels
|--------------------------------------------------------------------------
|
| Tags are the one catalog metadata whose cache key cannot self-heal: nothing in
| a request for `/` reflects which tags are featured, so `TagObserver` is the
| only thing that makes a reconfiguration visible before the TTL.
|
| It differs from the other metadata observers in three ways, each pinned below:
| a create counts, an empty change set (a synced attribute_tag pivot) counts, and
| only the *lists* are flushed — no product page renders a tag, so the product
| pages must stay warm.
*/

it('flushes the lists when a tag is created', function () {
    // Unlike a new category or brand, a new featured tag is immediately visible.
    [, $detail] = primedDetail();
    $list = primedList('tag-create');

    Tag::create(['name' => 'تگ تازه', 'slug' => 'obs-fresh', 'show_on_home' => true, 'home_order' => 1]);

    expect($list())->toBeNull()
        // ...but a tag reaches no product page, so those stay warm.
        ->and($detail())->not->toBeNull();
});

it('flushes the lists when a tag is featured or unfeatured', function () {
    $tag = Tag::create(['name' => 'تگ', 'slug' => 'obs-feature', 'show_on_home' => false]);
    $list = primedList('tag-feature');

    $tag->update(['show_on_home' => true]);

    expect($list())->toBeNull();
});

it('flushes the lists when a tag is reordered', function () {
    $tag = Tag::create(['name' => 'تگ', 'slug' => 'obs-order', 'show_on_home' => true, 'home_order' => 1]);
    $list = primedList('tag-order');

    $tag->update(['home_order' => 5]);

    expect($list())->toBeNull();
});

it('flushes the lists when a tag is deleted', function () {
    $tag = Tag::create(['name' => 'تگ', 'slug' => 'obs-delete', 'show_on_home' => true]);
    $list = primedList('tag-delete');

    $tag->delete();

    expect($list())->toBeNull();
});

it('flushes the lists when only a tag attribute pivot is synced', function () {
    // TagResource syncs attribute_tag after saving the record, so every tag
    // column comes out clean while what the carousel matches has changed. That
    // is why TagObserver treats an empty change set as "flush".
    $tag = Tag::create(['name' => 'تگ', 'slug' => 'obs-pivot', 'show_on_home' => true]);
    $attribute = Attribute::factory()->create();
    $list = primedList('tag-pivot');

    $tag->attributes()->sync([$attribute->id]);
    $tag->save();

    expect($list())->toBeNull();
});

it('keeps the lists when only tag SEO copy changes', function () {
    // content/title/description/no_index/canonical render on the tag's own page,
    // which is not cached.
    $tag = Tag::create(['name' => 'تگ', 'slug' => 'obs-seo', 'show_on_home' => true]);
    $list = primedList('tag-seo');

    $tag->update(['content' => '<p>متن</p>', 'title' => 'عنوان', 'no_index' => true]);

    expect($list())->toBe(['warm']);
});
