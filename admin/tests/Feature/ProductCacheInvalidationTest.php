<?php

declare(strict_types=1);

use App\Enums\OrderStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\ReviewStatusEnum;
use App\Filament\Resources\ProductResource;
use App\Models\Image;
use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\Product;
use App\Models\Review;
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
