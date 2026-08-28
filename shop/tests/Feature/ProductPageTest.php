<?php

declare(strict_types=1);

use App\Enums\CategoryStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\ReviewStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Review;
use App\Models\Variety;
use Illuminate\Support\Facades\DB;
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

it('pairs descriptive specs with their attribute group name and flags highlighted ones', function (): void {
    $category = Category::create([
        'heading' => 'پوشاک زنانه',
        'slug' => 'womens-clothing',
        'status' => CategoryStatusEnum::ACTIVE,
    ]);

    $ancestorId = DB::table('ancestors')->insertGetId([
        'name' => 'مشخصات فنی', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $groupId = DB::table('attribute_groups')->insertGetId([
        'ancestor_id' => $ancestorId, 'name' => 'متریال', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $cotton = Attribute::create(['attribute_group_id' => $groupId, 'value' => 'پنبه']);

    $product = Product::create([
        'heading' => 'شومیز زنانه',
        'slug' => 'womens-blouse',
        'price' => 700000,
        'category_id' => $category->id,
        'status' => ProductStatusEnum::PUBLISHED,
        'seen' => 0,
    ]);
    makeImage(Product::class, $product->id);
    $product->attributes()->attach($cotton->id, ['is_highlight' => true]);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('product.specs', 1, fn (AssertableInertia $spec): AssertableInertia => $spec
                ->where('group', 'متریال')
                ->where('highlight', true)
                ->has('values', 1, fn (AssertableInertia $value): AssertableInertia => $value
                    ->where('value', 'پنبه')
                    ->where('color', null)
                )
            )
        );
});

it('groups several values in the same spec group into one row', function (): void {
    $category = Category::create([
        'heading' => 'کیف و کفش',
        'slug' => 'bags-shoes',
        'status' => CategoryStatusEnum::ACTIVE,
    ]);

    $ancestorId = DB::table('ancestors')->insertGetId([
        'name' => 'ویژگی‌های ظاهری', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $groupId = DB::table('attribute_groups')->insertGetId([
        'ancestor_id' => $ancestorId, 'name' => 'سایز کفش', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $sizes = collect(['40', '41', '42'])
        ->map(fn (string $value): Attribute => Attribute::create(['attribute_group_id' => $groupId, 'value' => $value]));

    $product = Product::create([
        'heading' => 'کفش رانینگ',
        'slug' => 'running-shoes',
        'price' => 1200000,
        'category_id' => $category->id,
        'status' => ProductStatusEnum::PUBLISHED,
        'seen' => 0,
    ]);
    makeImage(Product::class, $product->id);
    $product->attributes()->attach($sizes->pluck('id')->all());

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('product.specs', 1, fn (AssertableInertia $spec): AssertableInertia => $spec
                ->where('group', 'سایز کفش')
                ->where('highlight', false)
                ->has('values', 3)
            )
        );
});

it('builds a selectable axis from variety attributes', function (): void {
    $category = Category::create([
        'heading' => 'پوشاک',
        'slug' => 'apparel',
        'status' => CategoryStatusEnum::ACTIVE,
    ]);

    $ancestorId = DB::table('ancestors')->insertGetId([
        'name' => 'رنگ',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $groupId = DB::table('attribute_groups')->insertGetId([
        'ancestor_id' => $ancestorId,
        'name' => 'رنگ ظاهری',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $green = Attribute::create(['attribute_group_id' => $groupId, 'value' => 'سبز', 'color' => '#003300']);
    $blue = Attribute::create(['attribute_group_id' => $groupId, 'value' => 'آبی', 'color' => '#000099']);

    $product = Product::create([
        'heading' => 'ست تیشرت',
        'slug' => 'tshirt-set',
        'price' => 500000,
        'category_id' => $category->id,
        'status' => ProductStatusEnum::PUBLISHED,
        'seen' => 0,
    ]);
    makeImage(Product::class, $product->id);

    Variety::create([
        'product_id' => $product->id,
        'attribute_id' => $green->id,
        'attribute_value' => 'سبز',
        'color' => '#003300',
        'price' => 500000,
        'inventory' => 25,
        'has_stock' => true,
        'status' => VarietyStatusEnum::PUBLISHED,
    ]);
    Variety::create([
        'product_id' => $product->id,
        'attribute_id' => $blue->id,
        'attribute_value' => 'آبی',
        'color' => '#000099',
        'price' => 500000,
        'inventory' => 0,
        'has_stock' => true,
        'status' => VarietyStatusEnum::PUBLISHED,
    ]);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('product.variantAxes', 1, fn (AssertableInertia $axis): AssertableInertia => $axis
                ->where('name', 'رنگ ظاهری')
                ->has('options', 2)
                ->etc()
            )
            ->has('product.varieties', 2)
            ->has('product.varieties.0.options')
        );
});

it('orders variant axes and their options deterministically, primary first', function (): void {
    $category = Category::create([
        'heading' => 'کفش', 'slug' => 'shoes', 'status' => CategoryStatusEnum::ACTIVE,
    ]);

    $ancestorId = DB::table('ancestors')->insertGetId([
        'name' => 'مشخصات', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $sizeGroup = DB::table('attribute_groups')->insertGetId([
        'ancestor_id' => $ancestorId, 'name' => 'سایز', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $colorGroup = DB::table('attribute_groups')->insertGetId([
        'ancestor_id' => $ancestorId, 'name' => 'رنگ', 'created_at' => now(), 'updated_at' => now(),
    ]);

    // Attributes created in logical order: S, M, L, XL.
    $s = Attribute::create(['attribute_group_id' => $sizeGroup, 'value' => 'S']);
    $m = Attribute::create(['attribute_group_id' => $sizeGroup, 'value' => 'M']);
    $l = Attribute::create(['attribute_group_id' => $sizeGroup, 'value' => 'L']);
    $xl = Attribute::create(['attribute_group_id' => $sizeGroup, 'value' => 'XL']);
    $red = Attribute::create(['attribute_group_id' => $colorGroup, 'value' => 'قرمز']);

    $product = Product::create([
        'heading' => 'کفش ورزشی', 'slug' => 'sneaker', 'price' => 900000,
        'category_id' => $category->id, 'status' => ProductStatusEnum::PUBLISHED, 'seen' => 0,
    ]);
    makeImage(Product::class, $product->id);

    // First variety has NO primary attribute (simulates a deleted primary
    // attribute) but does carry a secondary (Color) attribute — this used to
    // push the Color axis above the Size axis.
    $noPrimary = Variety::create([
        'product_id' => $product->id, 'price' => 900000, 'inventory' => 5,
        'has_stock' => true, 'status' => VarietyStatusEnum::PUBLISHED,
    ]);
    $noPrimary->attributes()->attach($red->id);

    // Remaining varieties created in a shuffled order: XL, L, S, M.
    foreach ([$xl, $l, $s, $m] as $attribute) {
        $variety = Variety::create([
            'product_id' => $product->id, 'attribute_id' => $attribute->id, 'price' => 900000,
            'inventory' => 5, 'has_stock' => true, 'status' => VarietyStatusEnum::PUBLISHED,
        ]);
        $variety->attributes()->attach($red->id);
    }

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('product.variantAxes.0.name', 'سایز')
            ->where('product.variantAxes.0.primary', true)
            ->where('product.variantAxes.1.name', 'رنگ')
            ->where('product.variantAxes.0.options.0.value', 'S')
            ->where('product.variantAxes.0.options.1.value', 'M')
            ->where('product.variantAxes.0.options.2.value', 'L')
            ->where('product.variantAxes.0.options.3.value', 'XL')
        );
});
