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

/*
|--------------------------------------------------------------------------
| Caching (CACHE.md key 13)
|--------------------------------------------------------------------------
|
| Both product carousel groups are cached: the newest/most-viewed rows and the
| per-tag rows. The tag rows were the expensive half — every row costs its own
| category walk, attribute grouping and product query.
|
| Tags are the only catalog metadata a *cache key* cannot self-heal from:
| nothing in a request for `/` reflects which tags are featured, so `TagObserver`
| is what makes a reconfiguration visible. Several tests below fail without it.
*/

it('serves the home page carousels from cache on the second visit', function (): void {
    $category = catCategory('cache-home-cat');
    foreach (range(1, 15) as $ignored) {
        catProduct($category);
    }
    foreach (range(1, 6) as $i) {
        Tag::create(['name' => 'c'.$i, 'slug' => 'c-tag-'.$i, 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => $i]);
    }

    $countQueries = function (): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/')->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    $cold = $countQueries();
    $warm = $countQueries();

    // The carousels are the bulk of this page. What survives on a warm request
    // is the layout that is not cached yet (banners, sliders, the category strip
    // and nav, settings, featured brands) plus the per-visitor cart count.
    expect($warm)->toBeLessThan((int) ($cold / 2))
        ->and($cold)->toBeGreaterThan(40);
});

it('shows a newly featured tag on the next home page request', function (): void {
    // The reason TagObserver exists. The cached payload was built when this tag
    // was not featured, and nothing in a `/` request would produce a new key.
    $category = catCategory('feature-cat');
    catProduct($category);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->has('tagRows', 0));

    Tag::create(['name' => 'تگ تازه', 'slug' => 'fresh-tag', 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => 1]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('tagRows', 1)
            ->where('tagRows.0.title', 'تگ تازه')
        );
});

it('drops a tag from the home page when it stops being featured', function (): void {
    $category = catCategory('unfeature-cat');
    catProduct($category);
    $tag = Tag::create(['name' => 'تگ رفتنی', 'slug' => 'going-tag', 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => 1]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->has('tagRows', 1));

    $tag->update(['show_on_home' => false]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->has('tagRows', 0));
});

it('reorders the home carousels when a tag home_order changes', function (): void {
    $category = catCategory('reorder-cat');
    catProduct($category);
    $first = Tag::create(['name' => 'اول', 'slug' => 'ord-1', 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => 1]);
    Tag::create(['name' => 'دوم', 'slug' => 'ord-2', 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => 2]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('tagRows.0.title', 'اول'));

    $first->update(['home_order' => 9]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('tagRows.0.title', 'دوم'));
});

it('renames a home carousel when its tag is renamed', function (): void {
    $category = catCategory('tag-rename-cat');
    catProduct($category);
    $tag = Tag::create(['name' => 'نام قبلی', 'slug' => 'rename-tag', 'category_id' => $category->id, 'show_on_home' => true, 'home_order' => 1]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('tagRows.0.title', 'نام قبلی'));

    $tag->update(['name' => 'نام تازه']);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('tagRows.0.title', 'نام تازه'));
});

it('shows a newly published product in the newest carousel', function (): void {
    // Covered by ProductObserver rather than TagObserver, but the home rows are
    // a new consumer of that invalidation, so it is worth pinning end to end.
    $category = catCategory('newest-cat');
    catProduct($category);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->has('productRows.0.products', 1));

    catProduct($category);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->has('productRows.0.products', 2));
});
