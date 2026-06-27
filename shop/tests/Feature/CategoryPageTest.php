<?php

declare(strict_types=1);

use App\Enums\BrandStatusEnum;
use App\Enums\CategoryStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Variety;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

function catImage(string $type, int $id): void
{
    Image::create([
        'path' => 'media/sample.jpg',
        'imageable_type' => $type,
        'imageable_id' => $id,
        'alt_text' => 'تصویر نمونه',
        'is_featured' => true,
    ]);
}

function catCategory(string $slug = 'shoes', ?int $parentId = null): Category
{
    return Category::create([
        'heading' => 'دسته '.$slug,
        'slug' => $slug,
        'status' => CategoryStatusEnum::ACTIVE,
        'parent_id' => $parentId,
    ]);
}

function catProduct(Category $category, int $price = 100000, ?int $brandId = null, ?int $attributeId = null, bool $hasStock = true): Product
{
    $product = Product::create([
        'heading' => 'محصول '.uniqid(),
        'slug' => 'product-'.uniqid(),
        'price' => $price,
        'category_id' => $category->id,
        'brand_id' => $brandId,
        'has_stock' => $hasStock,
        'status' => ProductStatusEnum::PUBLISHED,
        'seen' => 0,
    ]);
    catImage(Product::class, $product->id);

    Variety::create([
        'product_id' => $product->id,
        'price' => $price,
        'inventory' => 5,
        'has_stock' => true,
        'status' => VarietyStatusEnum::PUBLISHED,
    ]);

    // product_attribute is the documented filters-to-products link.
    if ($attributeId !== null) {
        $product->attributes()->attach($attributeId, ['is_highlight' => false]);
    }

    return $product;
}

it('renders the category page with products and breadcrumbs', function (): void {
    $category = catCategory('apparel');
    catProduct($category);

    $this->get('/categories/'.$category->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Category/Show')
            ->where('category.heading', $category->heading)
            ->has('products.data', 1)
            ->where('products.meta.total', 1)
            ->has('breadcrumbs', 2)
            ->where('breadcrumbs.0.heading', 'خانه')
        );
});

it('includes products from descendant categories', function (): void {
    $parent = catCategory('electronics');
    $child = catCategory('phones', $parent->id);
    catProduct($child);

    $this->get('/categories/'.$parent->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 1)
        );
});

it('filters products by brand', function (): void {
    $category = catCategory('bags');
    $nike = Brand::create(['heading' => 'نایک', 'slug' => 'nike', 'status' => BrandStatusEnum::ACTIVE]);
    $puma = Brand::create(['heading' => 'پوما', 'slug' => 'puma', 'status' => BrandStatusEnum::ACTIVE]);
    catProduct($category, brandId: $nike->id);
    catProduct($category, brandId: $puma->id);

    $this->get('/categories/'.$category->slug.'?brands[]=nike')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 1)
            ->has('filters.brands', 2)
        );
});

it('filters products by price range', function (): void {
    $category = catCategory('watches');
    catProduct($category, price: 100000);
    catProduct($category, price: 500000);

    $this->get('/categories/'.$category->slug.'?min_price=200000')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 1)
            ->where('filters.price.min', 100000)
            ->where('filters.price.max', 500000)
        );
});

it('filters products by a product attribute', function (): void {
    $category = catCategory('tshirts');

    $ancestorId = DB::table('ancestors')->insertGetId([
        'name' => 'رنگ', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $groupId = DB::table('attribute_groups')->insertGetId([
        'ancestor_id' => $ancestorId, 'name' => 'رنگ', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('attribute_group_category')->insert([
        'attribute_group_id' => $groupId,
        'category_id' => $category->id,
        'as_filter' => true,
        'required' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $red = Attribute::create(['attribute_group_id' => $groupId, 'value' => 'قرمز', 'color' => '#ff0000']);
    $blue = Attribute::create(['attribute_group_id' => $groupId, 'value' => 'آبی', 'color' => '#0000ff']);

    catProduct($category, attributeId: $red->id);
    catProduct($category, attributeId: $blue->id);

    $this->get('/categories/'.$category->slug.'?attributes[]='.$red->id)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 1)
            ->has('filters.attributeGroups', 1, fn (AssertableInertia $group): AssertableInertia => $group
                ->where('name', 'رنگ')
                ->has('attributes', 2)
                ->etc()
            )
        );
});

it('filters products to in-stock only', function (): void {
    $category = catCategory('stock');
    catProduct($category, hasStock: true);
    catProduct($category, hasStock: false);

    $this->get('/categories/'.$category->slug.'?in_stock=1')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 1)
        );
});

it('exposes per-brand product counts in facets', function (): void {
    $category = catCategory('counts');
    $brand = Brand::create(['heading' => 'سونی', 'slug' => 'sony', 'status' => BrandStatusEnum::ACTIVE]);
    catProduct($category, brandId: $brand->id);
    catProduct($category, brandId: $brand->id);

    $this->get('/categories/'.$category->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('filters.brands.0.count', 2)
            ->etc()
        );
});

it('sorts products by cheapest first', function (): void {
    $category = catCategory('sorting');
    catProduct($category, price: 900000);
    catProduct($category, price: 100000);

    $this->get('/categories/'.$category->slug.'?sort=cheapest')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.data.0.price', 100000)
        );
});

it('paginates products', function (): void {
    $category = catCategory('many');

    foreach (range(1, 25) as $ignored) {
        catProduct($category);
    }

    $this->get('/categories/'.$category->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.total', 25)
            ->where('products.meta.lastPage', 2)
            ->has('products.data', 24)
        );

    $this->get('/categories/'.$category->slug.'?page=2')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('products.meta.currentPage', 2)
            ->has('products.data', 1)
        );
});

it('returns 404 for an inactive category', function (): void {
    $category = Category::create([
        'heading' => 'غیرفعال',
        'slug' => 'inactive-cat',
        'status' => CategoryStatusEnum::INACTIVE,
    ]);

    $this->get('/categories/'.$category->slug)->assertNotFound();
});

it('returns 404 for a missing category', function (): void {
    $this->get('/categories/does-not-exist')->assertNotFound();
});
