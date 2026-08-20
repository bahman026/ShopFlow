<?php

declare(strict_types=1);

use App\Enums\BannerStatusEnum;
use App\Enums\BrandStatusEnum;
use App\Enums\CategoryStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\SliderPositionEnum;
use App\Enums\SliderStatusEnum;
use App\Enums\VarietyStatusEnum;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Slide;
use App\Models\Slider;
use App\Models\Tag;
use App\Models\Variety;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

function featureImage(string $type, int $id, bool $featured = true): void
{
    Image::create([
        'path' => 'media/sample.jpg',
        'imageable_type' => $type,
        'imageable_id' => $id,
        'alt_text' => 'تصویر نمونه',
        'is_featured' => $featured,
    ]);
}

it('renders the home page with the catalog sections', function (): void {
    $category = Category::create([
        'heading' => 'پوشاک',
        'slug' => 'clothing',
        'status' => CategoryStatusEnum::ACTIVE,
    ]);
    featureImage(Category::class, $category->id, false);

    $product = Product::create([
        'heading' => 'پیراهن مردانه',
        'slug' => 'mens-shirt',
        'price' => 100000,
        'category_id' => $category->id,
        'status' => ProductStatusEnum::PUBLISHED,
        'seen' => 50,
    ]);
    featureImage(Product::class, $product->id);
    Variety::create([
        'product_id' => $product->id,
        'price' => 100000,
        'sale_price' => 80000,
        'inventory' => 5,
        'has_stock' => true,
        'status' => VarietyStatusEnum::PUBLISHED,
    ]);

    $slider = Slider::create([
        'name' => 'home slider',
        'position' => 'home-main',
        'status' => SliderStatusEnum::PUBLISHED,
    ]);
    $slide = Slide::create([
        'slider_id' => $slider->id,
        'heading' => 'حراج بهاره',
        'url' => '/campaign',
        'order' => 1,
    ]);
    featureImage(Slide::class, $slide->id, false);

    $banner = Banner::create([
        'position' => 'home-middle',
        'heading' => 'بنر تبلیغاتی',
        'url' => '/promo',
        'sort' => 1,
        'status' => BannerStatusEnum::PUBLISHED,
    ]);
    featureImage(Banner::class, $banner->id);

    $brand = Brand::create([
        'heading' => 'نایک',
        'slug' => 'nike',
        'status' => BrandStatusEnum::ACTIVE,
    ]);
    featureImage(Brand::class, $brand->id, false);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->has('slides', 1)
            ->has('categories', 1)
            ->has('banners', 1)
            ->has('brands', 1)
            ->has('productRows', 2)
            ->has('productRows.0.products', 1, fn (AssertableInertia $card): AssertableInertia => $card
                ->where('heading', 'پیراهن مردانه')
                ->where('price', 100000)
                ->where('salePrice', 80000)
                ->where('discountPercent', 20)
                ->etc()
            )
        );
});

it('shares header navigation categories with their children', function (): void {
    $parent = Category::create([
        'heading' => 'لوازم جانبی گوشی',
        'slug' => 'mobile-accessories',
        'status' => CategoryStatusEnum::ACTIVE,
    ]);
    Category::create([
        'heading' => 'قاب و کاور',
        'slug' => 'phone-cases',
        'parent_id' => $parent->id,
        'status' => CategoryStatusEnum::ACTIVE,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('nav.categories', 1, fn (AssertableInertia $category): AssertableInertia => $category
                ->where('heading', 'لوازم جانبی گوشی')
                ->where('url', '/categories/mobile-accessories')
                ->has('children', 1)
                ->etc()
            )
        );
});

it('renders the home page when the catalog is empty', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->where('slides', [])
            ->where('productRows', [])
            ->where('tagRows', [])
            ->where('brands', [])
        );
});

it('shows a product carousel for each featured tag, in order', function (): void {
    $category = catCategory('tag-cat');
    catProduct($category);

    Tag::create(['name' => 'تگ دوم', 'slug' => 'tag-b', 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => 2]);
    Tag::create(['name' => 'تگ اول', 'slug' => 'tag-a', 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => 1]);
    Tag::create(['name' => 'تگ مخفی', 'slug' => 'tag-hidden', 'category_id' => $category->id, 'show_on_home' => false]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('tagRows', 2) // only the two featured, not the hidden one
            ->where('tagRows.0.title', 'تگ اول') // home_order 1 first
            ->where('tagRows.0.viewAllUrl', '/tags/tag-a')
            ->has('tagRows.0.products', 1)
            ->where('tagRows.1.title', 'تگ دوم')
        );
});

it('drops a featured tag whose filter matches no products', function (): void {
    $withProducts = catCategory('tag-with-products');
    catProduct($withProducts);
    $empty = catCategory('tag-without-products');

    Tag::create(['name' => 'پر', 'slug' => 'tag-full', 'category_id' => $withProducts->id, 'show_on_home' => true, 'home_order' => 1]);
    Tag::create(['name' => 'خالی', 'slug' => 'tag-empty', 'category_id' => $empty->id, 'show_on_home' => true, 'home_order' => 2]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            // An empty carousel is worse than no carousel.
            ->has('tagRows', 1)
            ->where('tagRows.0.title', 'پر')
        );
});

it('only shows the slider assigned to the home-main position', function (): void {
    // A published slider at a different position must not leak onto the home
    // hero — the home page asks GetSliderByPosition for HOME_MAIN only.
    $other = Slider::create([
        'name' => 'sidebar slider',
        'position' => SliderPositionEnum::PRODUCT_SIDE->value,
        'status' => SliderStatusEnum::PUBLISHED,
    ]);
    Slide::create(['slider_id' => $other->id, 'heading' => 'کنار', 'order' => 1]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('slides', []));
});

it('caps how many tag carousels the home page shows', function (): void {
    $category = catCategory('capped-cat');
    catProduct($category);

    // Ten featured tags, all with products; only the first six by home_order
    // may render — each row costs its own queries, so the page has to stay
    // bounded no matter how many tags staff feature.
    foreach (range(1, 10) as $i) {
        Tag::create([
            'name' => 'تگ '.$i,
            'slug' => 'capped-tag-'.$i,
            'category_id' => $category->id,
            'show_on_home' => true,
            'home_order' => $i,
        ]);
    }

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('tagRows', 6)
            ->where('tagRows.0.title', 'تگ 1')
            ->where('tagRows.5.title', 'تگ 6')
        );
});

it('keeps the home page query count flat as more tags are featured', function (): void {
    $category = catCategory('query-cat');
    catProduct($category);

    $countQueries = function (): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/')->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    foreach (range(1, 6) as $i) {
        Tag::create(['name' => 'q'.$i, 'slug' => 'q-tag-'.$i, 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => $i]);
    }

    $atCap = $countQueries();

    // Four more featured tags must cost nothing: they are past the cap.
    foreach (range(7, 10) as $i) {
        Tag::create(['name' => 'q'.$i, 'slug' => 'q-tag-'.$i, 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => $i]);
    }

    expect($countQueries())->toBe($atCap);
});
