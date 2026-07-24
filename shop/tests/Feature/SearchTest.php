<?php

declare(strict_types=1);

use App\Enums\BrandStatusEnum;
use App\Enums\CategoryStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

function searchProduct(string $heading, ?int $brandId = null, string $status = 'published'): Product
{
    return Product::create([
        'heading' => $heading,
        'slug' => Str::slug($heading).'-'.uniqid(),
        'price' => 100000,
        'category_id' => catCategory('search-cat-'.uniqid())->id,
        'brand_id' => $brandId,
        'has_stock' => true,
        'status' => $status === 'published' ? ProductStatusEnum::PUBLISHED : ProductStatusEnum::DRAFT,
        'seen' => 0,
    ]);
}

it('finds products whose name matches the query', function (): void {
    $match = searchProduct('کفش ورزشی مردانه');
    searchProduct('کیف چرمی زنانه');

    $this->get('/search?q='.urlencode('کفش'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Search/Results')
            ->where('query', 'کفش')
            ->has('products.data', 1)
            ->where('products.data.0.heading', $match->heading)
        );
});

it('finds products by their brand name', function (): void {
    $brand = Brand::create([
        'heading' => 'نایک',
        'slug' => 'nike',
        'status' => BrandStatusEnum::ACTIVE,
    ]);
    searchProduct('کفش دونده', $brand->id);
    searchProduct('کیف اداری');

    $this->get('/search?q='.urlencode('نایک'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('products.data', 1)
        );
});

it('excludes unpublished products from search results', function (): void {
    searchProduct('ساعت هوشمند', status: 'draft');

    $this->get('/search?q='.urlencode('ساعت'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('products.data', 0)
        );
});

it('returns no results for a blank query', function (): void {
    searchProduct('هر محصولی');

    $this->get('/search')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Search/Results')
            ->where('query', '')
            ->where('products.meta.total', 0)
        );
});

it('returns category and product suggestions as json', function (): void {
    Category::create([
        'heading' => 'تلویزیون',
        'slug' => 'tv',
        'status' => CategoryStatusEnum::ACTIVE,
    ]);
    searchProduct('تلویزیون سامسونگ');
    searchProduct('یخچال');

    $this->getJson('/search/suggest?q='.urlencode('تلویزیون'))
        ->assertOk()
        ->assertJsonCount(1, 'categories')
        ->assertJsonCount(1, 'products')
        ->assertJsonPath('categories.0.heading', 'تلویزیون')
        ->assertJsonPath('products.0.heading', 'تلویزیون سامسونگ');
});
