<?php

declare(strict_types=1);

use App\Enums\CategoryStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\ReviewStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Review;
use App\Models\Variety;
use Inertia\Testing\AssertableInertia;

function makeImage(string $type, int $id, bool $featured = true): void
{
    Image::create([
        'path' => 'media/sample.jpg',
        'imageable_type' => $type,
        'imageable_id' => $id,
        'alt_text' => 'تصویر نمونه',
        'is_featured' => $featured,
    ]);
}

function makeProduct(): Product
{
    $category = Category::create([
        'heading' => 'پوشاک مردانه',
        'slug' => 'mens-clothing',
        'status' => CategoryStatusEnum::ACTIVE,
    ]);

    $product = Product::create([
        'heading' => 'پولوشرت مردانه نقرآبی',
        'slug' => 'mens-polo-shirt',
        'price' => 1989000,
        'content' => '<p>یک پولوشرت باکیفیت.</p>',
        'title' => 'خرید پولوشرت مردانه',
        'description' => 'بهترین پولوشرت مردانه',
        'category_id' => $category->id,
        'status' => ProductStatusEnum::PUBLISHED,
        'seen' => 10,
    ]);
    makeImage(Product::class, $product->id);

    Variety::create([
        'product_id' => $product->id,
        'attribute_value' => 'L',
        'color' => '#0a58ca',
        'price' => 1989000,
        'sale_price' => 1489000,
        'inventory' => 5,
        'has_stock' => true,
        'status' => VarietyStatusEnum::PUBLISHED,
    ]);

    return $product;
}

it('renders the product page with details, varieties and breadcrumbs', function (): void {
    $product = makeProduct();

    Review::create([
        'heading' => 'عالی بود',
        'content' => 'کیفیت خوبی داشت.',
        'product_id' => $product->id,
        'status' => ReviewStatusEnum::APPROVED,
    ]);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Product/Show')
            ->where('product.heading', 'پولوشرت مردانه نقرآبی')
            ->where('product.price', 1989000)
            ->where('product.salePrice', 1489000)
            ->where('product.discountPercent', 25)
            ->where('product.inStock', true)
            ->has('product.varieties', 1)
            ->has('product.reviews', 1)
            ->where('product.reviewCount', 1)
            ->has('breadcrumbs', 3)
            ->where('breadcrumbs.0.heading', 'خانه')
        );
});

it('counts a view on each visit', function (): void {
    $product = makeProduct();

    $this->get('/products/'.$product->slug)->assertOk();

    expect($product->refresh()->seen)->toBe(11);
});

it('returns related products from the same category', function (): void {
    $product = makeProduct();

    $related = Product::create([
        'heading' => 'تیشرت مردانه',
        'slug' => 'mens-tshirt',
        'price' => 500000,
        'category_id' => $product->category_id,
        'status' => ProductStatusEnum::PUBLISHED,
        'seen' => 0,
    ]);
    makeImage(Product::class, $related->id);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('related', 1)
            ->where('related.0.heading', 'تیشرت مردانه')
        );
});

it('returns 404 for an unpublished product', function (): void {
    $category = Category::create([
        'heading' => 'دسته',
        'slug' => 'draft-category',
        'status' => CategoryStatusEnum::ACTIVE,
    ]);

    $product = Product::create([
        'heading' => 'محصول پیش‌نویس',
        'slug' => 'draft-product',
        'price' => 100000,
        'category_id' => $category->id,
        'status' => ProductStatusEnum::DRAFT,
        'seen' => 0,
    ]);

    $this->get('/products/'.$product->slug)->assertNotFound();
});

it('returns 404 for a missing product', function (): void {
    $this->get('/products/does-not-exist')->assertNotFound();
});
