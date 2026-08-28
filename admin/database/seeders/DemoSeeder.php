<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DiscountForEnum;
use App\Enums\ReviewStatusEnum;
use App\Enums\SliderStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Ancestor;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeGroupCategory;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Slide;
use App\Models\Slider;
use App\Models\Tag;
use App\Models\User;
use App\Models\Variety;
use App\Support\ProductCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Loads the demo/staging catalog from demo/data/*.json — categories,
 * attributes, brands, ~50 products with variants and images, homepage
 * banners/sliders, tags, a header menu, and reviews. Every source file is
 * original/invented content generated for this dataset (with stock photos
 * from Pexels); nothing here is scraped from a third-party site.
 *
 * Reads only local files, makes no network calls, and is safe to run more
 * than once: every entity is looked up by its natural key (slug, email,
 * position+heading, …) before being created, and child rows with no natural
 * key of their own (varieties, slides, menu items, reviews) are cleared and
 * rebuilt from the same deterministic inputs each time — so a second run
 * converges on the same state instead of piling up duplicates.
 *
 * Deliberately NOT run in production: this is realistic-looking placeholder
 * content, not real catalog data. See DatabaseSeeder for the environment
 * gate that calls this.
 */
class DemoSeeder extends Seeder
{
    private const DATA_PATH = __DIR__ . '/../../../demo/data';

    private const IMAGE_BASE_PATH = 'demo';

    /** @var array<string, int> */
    private array $summary = [];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('DemoSeeder: refusing to run in production. Skipped.');
            Log::warning('DemoSeeder: run() called in production; skipped.');

            return;
        }

        if (! is_dir(self::DATA_PATH)) {
            $this->command->warn('DemoSeeder: demo/data/ not found — nothing to seed. Skipped.');

            return;
        }

        DB::transaction(function (): void {
            $ancestors = $this->seedAncestorsUsedByDemo();
            $groups = $this->seedAttributeGroupsAndValues($ancestors);
            $brands = $this->seedBrands();
            $categories = $this->seedCategories($groups);
            $this->seedProducts($categories, $brands, $groups);
            $this->seedDiscounts();
            $this->seedReviews();
            $this->seedHomepage();
            $this->seedTags($categories);
            $this->seedMenu();
        });

        // Products are re-seeded by slug and their varieties are wiped and
        // recreated, so cached storefront pages describe rows that are gone.
        // Flushing after the transaction commits, not inside it: a rolled-back
        // seed should not have thrown away a valid cache.
        ProductCache::flushAll();

        $this->printSummary();
    }

    // ---------------------------------------------------------------------
    // Attributes
    // ---------------------------------------------------------------------

    /**
     * @return array<string, Ancestor> keyed by ancestor name
     */
    private function seedAncestorsUsedByDemo(): array
    {
        $names = collect($this->readJson('attributes.json')['groups'])
            ->pluck('ancestor_name')
            ->unique();

        $ancestors = [];
        foreach ($names as $name) {
            $ancestors[$name] = Ancestor::query()->firstOrCreate(['name' => $name], ['order' => 0]);
        }

        return $ancestors;
    }

    /**
     * @param  array<string, Ancestor>  $ancestors
     * @return array<string, AttributeGroup> keyed by group name (سایز, سایز کفش, رنگ, جنس)
     */
    private function seedAttributeGroupsAndValues(array $ancestors): array
    {
        $groups = [];

        foreach ($this->readJson('attributes.json')['groups'] as $groupData) {
            $group = AttributeGroup::query()->firstOrCreate(
                ['name' => $groupData['name']],
                [
                    'ancestor_id' => $ancestors[$groupData['ancestor_name']]->id,
                    'label' => $groupData['label'],
                    'order' => 0,
                ],
            );
            $groups[$groupData['name']] = $group;
            $this->count('attribute_groups');

            foreach ($groupData['values'] as $value) {
                // رنگ's values carry a hex color; every other group is a plain
                // string list.
                [$stringValue, $color] = is_array($value) ? [$value['value'], $value['color'] ?? null] : [$value, null];

                Attribute::query()->firstOrCreate(
                    ['attribute_group_id' => $group->id, 'value' => $stringValue],
                    ['color' => $color],
                );
                $this->count('attributes');
            }
        }

        return $groups;
    }

    /** @return array<string, Attribute> keyed by "{groupName}:{value}" */
    private function attributeLookup(): array
    {
        return Attribute::query()->with('attributeGroup')->get()
            ->mapWithKeys(fn (Attribute $a): array => ["{$a->attributeGroup->name}:{$a->value}" => $a])
            ->all();
    }

    // ---------------------------------------------------------------------
    // Brands
    // ---------------------------------------------------------------------

    /** @return array<string, Brand> keyed by slug */
    private function seedBrands(): array
    {
        $brands = [];
        foreach ($this->readJson('brands.json') as $data) {
            $brand = Brand::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'heading' => $data['heading'],
                    'content' => $data['description'],
                    'title' => $data['heading'],
                    'description' => $data['description'],
                    'no_index' => false,
                    'status' => 1, // BrandStatusEnum::ACTIVE
                ],
            );

            // Real product photos are real, but these brand names are
            // invented — a real company's logo would be a false trademark
            // match. A generated initials placeholder (same convention
            // ImageFactory already uses for test data) avoids that entirely.
            $brand->image()->delete();
            $initial = mb_substr($data['heading'], 0, 1);
            $brand->image()->create([
                'path' => 'https://placehold.co/400x200/1f2937/ffffff?text=' . rawurlencode($initial),
                'is_featured' => true,
                'order' => 0,
                'alt_text' => $data['heading'],
            ]);

            $brands[$data['slug']] = $brand;
            $this->count('brands');
        }

        return $brands;
    }

    // ---------------------------------------------------------------------
    // Categories
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, AttributeGroup>  $groups
     * @return array<string, Category> keyed by slug (leaf categories only —
     *                                 the ones products actually belong to)
     */
    private function seedCategories(array $groups): array
    {
        $leaves = [];

        foreach ($this->readJson('categories.json') as $parentData) {
            $parent = Category::query()->updateOrCreate(
                ['slug' => $parentData['slug']],
                [
                    'heading' => $parentData['heading'],
                    'content' => $parentData['description'],
                    'title' => $parentData['heading'],
                    'description' => $parentData['description'],
                    'no_index' => false,
                    'parent_id' => null,
                    'status' => 1, // CategoryStatusEnum::ACTIVE
                ],
            );
            $this->attachCategoryImage($parent, "categories/{$parentData['slug']}.webp");
            $this->count('categories');

            foreach ($parentData['children'] as $childData) {
                $child = Category::query()->updateOrCreate(
                    ['slug' => $childData['slug']],
                    [
                        'heading' => $childData['heading'],
                        'content' => $childData['heading'],
                        'title' => $childData['heading'],
                        'description' => $childData['heading'],
                        'no_index' => false,
                        'parent_id' => $parent->id,
                        'status' => 1,
                    ],
                );
                $this->attachCategoryImage($child, "categories/{$childData['slug']}.webp");
                $this->count('categories');
                $leaves[$childData['slug']] = $child;

                // The primary dimension group is always a usable filter here;
                // رنگ is additionally linked to every leaf below once we know
                // which leaves exist, so color filtering works everywhere.
                $this->linkAttributeGroupToCategory($groups[$childData['attribute_group']], $child);
            }
        }

        // رنگ is either the primary dimension (accessories) or a secondary
        // one (clothing/shoes), but it is always a real, usable filter.
        foreach ($leaves as $leaf) {
            $this->linkAttributeGroupToCategory($groups['رنگ'], $leaf);
        }

        return $leaves;
    }

    private function linkAttributeGroupToCategory(AttributeGroup $group, Category $category): void
    {
        AttributeGroupCategory::query()->firstOrCreate(
            ['attribute_group_id' => $group->id, 'category_id' => $category->id],
            ['as_filter' => true, 'required' => false],
        );
    }

    // ---------------------------------------------------------------------
    // Products, varieties, reviews
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, Category>  $categories
     * @param  array<string, Brand>  $brands
     * @param  array<string, AttributeGroup>  $groups
     */
    private function seedProducts(array $categories, array $brands, array $groups): void
    {
        $attributes = $this->attributeLookup();

        foreach ($this->readJson('products.json') as $data) {
            $category = $categories[$data['category_slug']];
            $isColorPrimary = empty($data['sizes']) && empty($data['shoe_sizes']);
            $primaryGroupName = $isColorPrimary ? 'رنگ' : (isset($data['shoe_sizes']) ? 'سایز کفش' : 'سایز');

            $combos = $this->buildVarietyCombos($data, $isColorPrimary);
            $prices = array_map(fn (array $c): int => $c['price'], $combos);
            $basePrice = min($prices);

            $product = Product::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'heading' => $data['heading'],
                    'price' => $basePrice,
                    'content' => $this->buildProductContent($data),
                    'title' => $data['heading'],
                    'description' => $data['highlight_spec'],
                    'no_index' => false,
                    'canonical' => null,
                    'attribute_group_id' => $groups[$primaryGroupName]->id,
                    'category_id' => $category->id,
                    'brand_id' => $brands[$data['brand_slug']]->id,
                    'minimum' => 1,
                    'maximum' => null,
                    'step' => 1,
                    'profit_percent' => 15,
                    'has_stock' => true,
                    'weight' => null,
                    'length' => null,
                    'width' => null,
                    'height' => null,
                    'status' => 20, // ProductStatusEnum::PUBLISHED
                    'seen' => abs(crc32($data['slug'] . 'seen')) % 5000,
                ],
            );
            $this->count('products');

            $imagePaths = $this->attachProductImages($product, $data['slug']);
            $this->attachDescriptiveAttributes($product, $data, $combos, $attributes);
            $this->rebuildVarieties($product, $combos, $isColorPrimary, $primaryGroupName, $attributes, $imagePaths);
        }
    }

    /**
     * The size×color grid, thinned to a realistic ~70% subset (a product
     * rarely stocks every size in every color — see admin/docs/VARIETY_GUIDE.md)
     * with per-combo price/sale/inventory derived deterministically from a
     * hash of the product slug + combo, so re-seeding reproduces the exact
     * same catalog instead of reshuffling numbers on every run.
     *
     * @param  array<string, mixed>  $data
     * @return list<array{primary: string, secondary: ?string, price: int, sale_price: ?int, inventory: int, has_stock: bool}>
     */
    private function buildVarietyCombos(array $data, bool $isColorPrimary): array
    {
        $basePrice = (int) $data['base_price'];

        $pairs = [];
        if ($isColorPrimary) {
            foreach ($data['colors'] as $color) {
                $pairs[] = [$color, null];
            }
        } else {
            $primary = $data['sizes'] ?? $data['shoe_sizes'];
            foreach ($primary as $size) {
                foreach ($data['colors'] as $color) {
                    $pairs[] = [$size, $color];
                }
            }
        }

        $combos = [];
        foreach ($pairs as $i => [$primaryValue, $secondaryValue]) {
            $hash = crc32("{$data['slug']}|{$i}|{$primaryValue}|{$secondaryValue}");
            $keep = ($hash % 10) < 7;
            if (! $keep) {
                continue;
            }

            $jitterPercent = ($hash % 21) - 10; // -10..+10
            $price = (int) (round($basePrice * (1 + $jitterPercent / 100) / 1000) * 1000);
            $onSale = ($hash % 5) === 0; // ~20%
            $salePrice = $onSale ? (int) (round($price * 0.8 / 1000) * 1000) : null;

            $combos[] = [
                'primary' => (string) $primaryValue,
                'secondary' => $secondaryValue !== null ? (string) $secondaryValue : null,
                'price' => $price,
                'sale_price' => $salePrice,
                'inventory' => $hash % 35,
                'has_stock' => ($hash % 23) !== 0,
            ];
        }

        if ($combos === []) {
            // Deterministic thinning can (rarely) drop everything; keep the
            // product sellable with one guaranteed variety.
            [$primaryValue, $secondaryValue] = $pairs[0];
            $combos[] = [
                'primary' => (string) $primaryValue,
                'secondary' => $secondaryValue !== null ? (string) $secondaryValue : null,
                'price' => $basePrice,
                'sale_price' => null,
                'inventory' => 10,
                'has_stock' => true,
            ];
        }

        return $combos;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildProductContent(array $data): string
    {
        $materials = implode('، ', $data['materials']);

        return "<p>{$data['heading']} از برند {$this->brandHeadingPlaceholder($data['brand_slug'])} با جنس {$materials} تولید شده است. {$data['highlight_spec']}</p>" .
            '<p>این محصول با دقت در انتخاب مواد اولیه و دوخت تولید شده تا در استفاده روزمره ماندگاری خوبی داشته باشد. ' .
            'پیش از خرید، راهنمای سایزبندی را بررسی کنید و در صورت هرگونه سوال، پیش از ثبت سفارش با پشتیبانی فروشگاه در تماس باشید.</p>';
    }

    private function brandHeadingPlaceholder(string $slug): string
    {
        static $cache = null;
        $cache ??= Brand::query()->pluck('heading', 'slug')->all();

        return $cache[$slug] ?? $slug;
    }

    /** @return list<string> demo-relative image paths actually attached, e.g. "demo/products/x-1.webp" */
    private function attachProductImages(Product $product, string $slug): array
    {
        $product->images()->delete();

        $paths = [];
        foreach ([1, 2] as $n) {
            $relative = "products/{$slug}-{$n}.webp";
            if (! $this->demoImageExists($relative)) {
                continue;
            }
            $path = self::IMAGE_BASE_PATH . '/' . $relative;
            $product->images()->create([
                'path' => $path,
                'is_featured' => $n === 1,
                'order' => $n - 1,
                'alt_text' => $product->heading,
            ]);
            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array{primary: string, secondary: ?string}>  $combos
     * @param  array<string, Attribute>  $attributes
     */
    private function attachDescriptiveAttributes(Product $product, array $data, array $combos, array $attributes): void
    {
        $isColorPrimary = empty($data['sizes']) && empty($data['shoe_sizes']);
        $primaryGroupName = $isColorPrimary ? 'رنگ' : (isset($data['shoe_sizes']) ? 'سایز کفش' : 'سایز');

        // Every size/color/material string below is one this seeder itself
        // put into attributes.json, so each lookup always resolves.
        $sync = [];

        // Sizes (or, for accessories, colors) actually used by a kept combo —
        // not the full declared list, so the spec reflects what's orderable.
        $usedPrimary = collect($combos)->pluck('primary')->unique();
        foreach ($usedPrimary as $value) {
            $sync[$attributes["{$primaryGroupName}:{$value}"]->id] = ['is_highlight' => false];
        }

        // Secondary colors (clothing/shoes only — accessories already have
        // color as the primary group above).
        if (! $isColorPrimary) {
            $usedSecondary = collect($combos)->pluck('secondary')->filter()->unique();
            foreach ($usedSecondary as $value) {
                $sync[$attributes["رنگ:{$value}"]->id] = ['is_highlight' => false];
            }
        }

        // Materials: purely descriptive, never a filter (see جنس in
        // attributes.json). First one is the highlighted spec.
        foreach (array_values($data['materials']) as $i => $material) {
            $sync[$attributes["جنس:{$material}"]->id] = ['is_highlight' => $i === 0];
        }

        $product->attributes()->sync($sync);
    }

    /**
     * @param  list<array{primary: string, secondary: ?string, price: int, sale_price: ?int, inventory: int, has_stock: bool}>  $combos
     * @param  array<string, Attribute>  $attributes
     * @param  list<string>  $imagePaths
     */
    private function rebuildVarieties(Product $product, array $combos, bool $isColorPrimary, string $primaryGroupName, array $attributes, array $imagePaths): void
    {
        // Varieties have no natural external key of their own; clear and
        // rebuild from the same deterministic inputs so re-seeding converges
        // instead of accumulating rows. Variety's own `deleting` hook cleans
        // up each one's image row.
        $product->varieties()->get()->each->delete();

        foreach ($combos as $i => $combo) {
            // Every size/color/shoe-size string in products.json is one this
            // seeder itself put into attributes.json, so the lookup always
            // resolves — if it ever doesn't, that's a data-authoring bug
            // worth failing loudly on, not masking.
            $primaryAttr = $attributes["{$primaryGroupName}:{$combo['primary']}"];

            $variety = Variety::query()->create([
                'product_id' => $product->id,
                'attribute_id' => $primaryAttr->id,
                'attribute_value' => $primaryAttr->value,
                'color' => $primaryAttr->color,
                'price' => $combo['price'],
                'sale_price' => $combo['sale_price'],
                'inventory' => $combo['inventory'],
                'has_stock' => $combo['has_stock'],
                'status' => 20, // VarietyStatusEnum::PUBLISHED
            ]);

            if (! $isColorPrimary && $combo['secondary'] !== null) {
                $variety->attributes()->sync([$attributes["رنگ:{$combo['secondary']}"]->id]);
            }

            if ($imagePaths !== []) {
                $variety->image()->create([
                    'path' => $imagePaths[$i % count($imagePaths)],
                    'is_featured' => true,
                    'order' => 0,
                    'alt_text' => $variety->attribute_value,
                ]);
            }

            $this->count('varieties');
        }
    }

    // ---------------------------------------------------------------------
    // Discounts (quantity/percentage tiers on top of variety.sale_price)
    // ---------------------------------------------------------------------

    private function seedDiscounts(): void
    {
        $varieties = Variety::query()->where('has_stock', true)->orderBy('id')->get();

        foreach ($varieties as $i => $variety) {
            // Every 5th in-stock variety gets a real quantity discount, on
            // top of whichever ones already carry a plain sale_price.
            if ($i % 5 !== 0) {
                continue;
            }

            $hash = crc32("discount|{$variety->id}");
            Discount::query()->updateOrCreate(
                ['variety_id' => $variety->id, 'priority' => 0],
                [
                    'quantity' => 2 + ($hash % 3), // buy 2-4+
                    'is_percent' => true,
                    'amount' => 10 + ($hash % 16), // 10-25%
                    'started_at' => null,
                    'ended_at' => null,
                    'sold' => 0,
                    'max_sell' => null,
                    'max_sell_by_user' => null,
                    'is_for' => DiscountForEnum::EVERYONE->value,
                ],
            );
            $this->count('discounts');
        }
    }

    // ---------------------------------------------------------------------
    // Reviews (with dedicated, stable demo reviewer accounts)
    // ---------------------------------------------------------------------

    private const REVIEWER_NAMES = [
        ['علی', 'رضایی'], ['مریم', 'احمدی'], ['حسین', 'کریمی'], ['زهرا', 'موسوی'],
        ['محمد', 'نجفی'], ['فاطمه', 'حسینی'], ['امیر', 'صادقی'], ['سارا', 'محمدی'],
        ['رضا', 'قاسمی'], ['نگار', 'یوسفی'], ['کیانا', 'رستمی'], ['پویا', 'شریفی'],
        ['الناز', 'عابدی'], ['بهنام', 'رحیمی'], ['یاسمن', 'طاهری'],
    ];

    private const REVIEW_TEMPLATES = [
        5 => ['کیفیت عالی بود و دقیقاً مطابق عکس‌ها رسید.', 'از خریدم خیلی راضی‌ام، پیشنهاد می‌کنم.', 'جنس و دوخت فوق‌العاده است، ارزش خرید داره.'],
        4 => ['کیفیت خوبی داشت، فقط ارسال کمی طول کشید.', 'بدون مشکل خاصی رسید، راضی بودم.', 'نسبت به قیمتش کیفیت مناسبی داره.'],
        3 => ['در حد انتظار بود، نه عالی نه بد.', 'کیفیت متوسط بود ولی قابل قبول است.'],
        2 => ['کیفیت پارچه کمی پایین‌تر از انتظارم بود.', 'سایزبندی کمی متفاوت از جدول سایز بود.'],
        1 => ['متاسفانه با توضیحات همخوانی نداشت.'],
    ];

    /** @return list<User> */
    private function seedDemoReviewers(): array
    {
        $users = [];
        foreach (self::REVIEWER_NAMES as $i => [$first, $last]) {
            $email = sprintf('demo.reviewer.%d@shopflow.demo', $i + 1);
            $users[] = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'mobile' => sprintf('0912900%04d', $i + 1),
                    // The `hashed` cast on User::password hashes this on
                    // write — pass it plain, never pre-hash.
                    'password' => Str::random(32),
                    'status' => UserStatusEnum::ACTIVE->value,
                    'login_token_expire_time' => now()->addWeek(),
                ],
            );
        }
        $this->summary['demo_reviewers'] = count($users);

        return $users;
    }

    private function seedReviews(): void
    {
        $reviewers = $this->seedDemoReviewers();
        $products = Product::query()->get()->keyBy('slug');

        foreach ($this->readJson('products.json') as $data) {
            $product = $products[$data['slug']] ?? null;
            if (! $product) {
                continue;
            }

            // Reviews have no natural key of their own; rebuild from the same
            // deterministic inputs so re-seeding converges.
            Review::query()->where('product_id', $product->id)->delete();

            $count = (int) $data['review_count'];
            $ratingHint = (float) $data['rating_hint'];

            for ($i = 0; $i < $count; $i++) {
                $hash = crc32("{$data['slug']}|review|{$i}");
                $delta = ($hash % 3) - 1; // -1, 0, or +1 around the hint
                $rating = max(1, min(5, (int) round($ratingHint) + $delta));
                $templates = self::REVIEW_TEMPLATES[$rating];
                $content = $templates[$hash % count($templates)];
                $reviewer = $reviewers[$hash % count($reviewers)];

                Review::query()->create([
                    'heading' => trim("{$reviewer->first_name} {$reviewer->last_name}") . ' درباره ' . $data['heading'],
                    'content' => $content,
                    'rating' => $rating,
                    'user_id' => $reviewer->id,
                    'product_id' => $product->id,
                    'variety_id' => null,
                    'parent_id' => null,
                    'status' => ReviewStatusEnum::APPROVED->value,
                ]);
                $this->count('reviews');
            }
        }
    }

    // ---------------------------------------------------------------------
    // Homepage: banners + sliders
    // ---------------------------------------------------------------------

    private function seedHomepage(): void
    {
        $homepage = $this->readJson('homepage.json');

        $bannerIndex = [];
        foreach ($homepage['banners'] as $data) {
            $n = ($bannerIndex[$data['position']] = ($bannerIndex[$data['position']] ?? 0) + 1);

            $banner = Banner::query()->updateOrCreate(
                ['position' => $data['position'], 'heading' => $data['heading']],
                ['url' => $data['url'], 'sort' => $data['sort'], 'status' => 20], // BannerStatusEnum::PUBLISHED
            );

            $banner->images()->delete();
            $relative = "banners/{$data['position']}-{$n}.webp";
            if ($this->demoImageExists($relative)) {
                $banner->images()->create([
                    'path' => self::IMAGE_BASE_PATH . '/' . $relative,
                    'is_featured' => true,
                    'order' => 0,
                    'alt_text' => $data['heading'],
                ]);
            }
            $this->count('banners');
        }

        foreach ($homepage['sliders'] as $sliderData) {
            $slider = Slider::query()->firstOrCreate(
                ['position' => $sliderData['position'], 'name' => $sliderData['name']],
                ['status' => SliderStatusEnum::PUBLISHED->value],
            );

            // Slides have no natural key; rebuild them each run (this also
            // cleans up each slide's image via Slide's own deleting hook).
            $slider->slides()->get()->each->delete();

            foreach ($sliderData['slides'] as $slideData) {
                $slide = Slide::query()->create([
                    'slider_id' => $slider->id,
                    'heading' => $slideData['heading'],
                    'label' => $slideData['label'],
                    'url' => $slideData['url'],
                    'order' => $slideData['order'],
                ]);

                $relative = "sliders/{$sliderData['position']}-{$slideData['order']}.webp";
                if ($this->demoImageExists($relative)) {
                    $slide->image()->create([
                        'path' => self::IMAGE_BASE_PATH . '/' . $relative,
                        'is_featured' => true,
                        'order' => 0,
                        'alt_text' => $slideData['heading'],
                    ]);
                }
                $this->count('slides');
            }
            $this->count('sliders');
        }
    }

    // ---------------------------------------------------------------------
    // Tags
    // ---------------------------------------------------------------------

    /** @param array<string, Category> $categories */
    private function seedTags(array $categories): void
    {
        $attributes = $this->attributeLookup();
        // Tags reference plain color values, which only ever live in رنگ —
        // every one of these strings is one this seeder itself put there.
        $colorAttributeIds = fn (array $values): array => collect($values)
            ->map(fn (string $v): int => $attributes["رنگ:{$v}"]->id)
            ->all();

        foreach ($this->readJson('tags.json') as $data) {
            $tag = Tag::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'category_id' => $categories[$data['category_slug']]->id,
                    'content' => null,
                    'title' => $data['name'],
                    'description' => $data['name'],
                    'no_index' => false,
                    'canonical' => null,
                    'show_on_home' => $data['show_on_home'],
                    'home_order' => $data['home_order'],
                ],
            );

            $tag->attributes()->sync($colorAttributeIds($data['attribute_values']));

            $tag->image()->delete();
            $relative = "tags/{$data['slug']}.webp";
            if ($data['show_on_home'] && $this->demoImageExists($relative)) {
                $tag->image()->create([
                    'path' => self::IMAGE_BASE_PATH . '/' . $relative,
                    'is_featured' => true,
                    'order' => 0,
                    'alt_text' => $data['name'],
                ]);
            }
            $this->count('tags');
        }
    }

    // ---------------------------------------------------------------------
    // Header menu
    // ---------------------------------------------------------------------

    private function seedMenu(): void
    {
        $data = $this->readJson('menu.json');

        $menu = Menu::query()->updateOrCreate(
            ['position' => $data['position']],
            ['name' => $data['name'], 'status' => true],
        );

        // Menu items have no natural key; children first (parent_id only
        // nulls on delete, it doesn't cascade), then top-level, then rebuild.
        MenuItem::query()->where('menu_id', $menu->id)->whereNotNull('parent_id')->get()->each->delete();
        MenuItem::query()->where('menu_id', $menu->id)->whereNull('parent_id')->get()->each->delete();

        foreach ($data['items'] as $itemData) {
            $parent = MenuItem::query()->create([
                'menu_id' => $menu->id,
                'parent_id' => null,
                'name' => $itemData['name'],
                'url' => $itemData['url'],
                'label' => null,
                'order' => $itemData['order'],
            ]);
            $this->count('menu_items');

            foreach ($itemData['children'] as $childData) {
                MenuItem::query()->create([
                    'menu_id' => $menu->id,
                    'parent_id' => $parent->id,
                    'name' => $childData['name'],
                    'url' => $childData['url'],
                    'label' => null,
                    'order' => $childData['order'],
                ]);
                $this->count('menu_items');
            }
        }
        $this->count('menus');
    }

    // ---------------------------------------------------------------------
    // Shared helpers
    // ---------------------------------------------------------------------

    private function attachCategoryImage(Category $category, string $relative): void
    {
        $category->image()->delete();
        if (! $this->demoImageExists($relative)) {
            return;
        }
        $category->image()->create([
            'path' => self::IMAGE_BASE_PATH . '/' . $relative,
            'is_featured' => true,
            'order' => 0,
            'alt_text' => $category->heading,
        ]);
    }

    private function demoImageExists(string $relative): bool
    {
        return is_file(storage_path('app/public/' . self::IMAGE_BASE_PATH . '/' . $relative));
    }

    /** @return array<mixed> */
    private function readJson(string $filename): array
    {
        $path = self::DATA_PATH . '/' . $filename;
        if (! is_file($path)) {
            throw new \RuntimeException("DemoSeeder: missing demo/data/{$filename}. Run the fetch pipeline first (see demo/README.md).");
        }

        /** @var array<mixed> $data */
        $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }

    private function count(string $key): void
    {
        $this->summary[$key] = ($this->summary[$key] ?? 0) + 1;
    }

    private function printSummary(): void
    {
        $this->command->info('DemoSeeder finished:');
        foreach ($this->summary as $key => $n) {
            $this->command->line("  {$key}: {$n}");
        }
    }
}
