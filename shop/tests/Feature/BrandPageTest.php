<?php

declare(strict_types=1);

use App\Enums\BrandStatusEnum;
use App\Models\Brand;
use Inertia\Testing\AssertableInertia;

// Reuses catCategory / catProduct helpers defined in CategoryPageTest.php.

function brandBrand(?string $slug = null): Brand
{
    return Brand::create([
        'heading' => 'برند '.($slug ?? uniqid()),
        'slug' => $slug ?? 'brand-'.uniqid(),
        'status' => BrandStatusEnum::ACTIVE,
    ]);
}

it('renders the brand page with products and breadcrumbs', function (): void {
    $brand = brandBrand();
    $category = catCategory('br-cat-'.uniqid());
    catProduct($category, brandId: $brand->id);

    $this->get('/brands/'.$brand->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Brand/Show')
            ->where('brand.heading', $brand->heading)
            ->has('products.data', 1)
            ->where('products.meta.total', 1)
            ->has('breadcrumbs', 2)
            ->where('breadcrumbs.0.heading', 'خانه')
        );
});

it('filters brand products by category', function (): void {
    $brand = brandBrand();
    $shoes = catCategory('br-shoes-'.uniqid());
    $bags = catCategory('br-bags-'.uniqid());
    catProduct($shoes, brandId: $brand->id);
    catProduct($bags, brandId: $brand->id);

    $this->get('/brands/'.$brand->slug.'?categories[]='.$shoes->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 1)
            ->has('filters.categories', 2)
        );
});

it('filters brand products by price range', function (): void {
    $brand = brandBrand();
    $category = catCategory('br-price-'.uniqid());
    catProduct($category, price: 100000, brandId: $brand->id);
    catProduct($category, price: 500000, brandId: $brand->id);

    $this->get('/brands/'.$brand->slug.'?min_price=200000')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 1)
            ->where('filters.price.min', 100000)
            ->where('filters.price.max', 500000)
        );
});

it('filters brand products to in-stock only', function (): void {
    $brand = brandBrand();
    $category = catCategory('br-stock-'.uniqid());
    catProduct($category, brandId: $brand->id, hasStock: true);
    catProduct($category, brandId: $brand->id, hasStock: false);

    $this->get('/brands/'.$brand->slug.'?in_stock=1')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 1)
        );
});

it('sorts brand products by cheapest first', function (): void {
    $brand = brandBrand();
    $category = catCategory('br-sort-'.uniqid());
    catProduct($category, price: 900000, brandId: $brand->id);
    catProduct($category, price: 100000, brandId: $brand->id);

    $this->get('/brands/'.$brand->slug.'?sort=cheapest')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.data.0.price', 100000)
        );
});

it('exposes per-category product counts in facets', function (): void {
    $brand = brandBrand();
    $category = catCategory('br-counts-'.uniqid());
    catProduct($category, brandId: $brand->id);
    catProduct($category, brandId: $brand->id);

    $this->get('/brands/'.$brand->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('filters.categories.0.count', 2)
            ->etc()
        );
});

it('returns 404 for an inactive brand', function (): void {
    $brand = Brand::create([
        'heading' => 'غیرفعال',
        'slug' => 'inactive-brand-'.uniqid(),
        'status' => BrandStatusEnum::INACTIVE,
    ]);

    $this->get('/brands/'.$brand->slug)->assertNotFound();
});

it('returns 404 for a missing brand', function (): void {
    $this->get('/brands/does-not-exist')->assertNotFound();
});
