<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Position keys (admin-defined) the storefront home reads.
     */
    private const SLIDER_POSITION = 'home-main';

    private const BANNER_POSITION = 'home-middle';

    /**
     * How many items each product carousel shows.
     */
    private const ROW_LIMIT = 12;

    public function __invoke(): Response
    {
        return Inertia::render('Home', [
            'slides' => $this->slides(),
            'categories' => $this->categories(),
            'banners' => $this->banners(),
            'productRows' => $this->productRows(),
            'brands' => $this->brands(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function slides(): array
    {
        $slider = Slider::query()
            ->published()
            ->where('position', self::SLIDER_POSITION)
            ->with(['slides' => fn ($query) => $query->orderBy('order'), 'slides.image'])
            ->first();

        if ($slider === null) {
            return [];
        }

        return $slider->slides
            ->map(fn ($slide): array => [
                'id' => $slide->id,
                'heading' => $slide->heading,
                'label' => $slide->label,
                'url' => $slide->url,
                'image' => $this->image($slide->image),
            ])
            ->all();
    }

    /**
     * Top-level active categories for the quick-links strip.
     *
     * @return array<int, array<string, mixed>>
     */
    private function categories(): array
    {
        return Category::query()
            ->active()
            ->whereNull('parent_id')
            ->with('image')
            ->orderBy('heading')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'heading' => $category->heading,
                'url' => '/categories/'.$category->slug,
                'image' => $this->image($category->image),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function banners(): array
    {
        return Banner::query()
            ->published()
            ->where('position', self::BANNER_POSITION)
            ->with('featuredImage')
            ->orderBy('sort')
            ->get()
            ->map(fn (Banner $banner): array => [
                'id' => $banner->id,
                'heading' => $banner->heading,
                'url' => $banner->url,
                'image' => $this->image($banner->featuredImage),
            ])
            ->all();
    }

    /**
     * Two product carousels: newest and most viewed.
     *
     * @return array<int, array<string, mixed>>
     */
    private function productRows(): array
    {
        $rows = [
            ['title' => 'جدیدترین محصولات', 'viewAllUrl' => '/products?sort=newest', 'query' => fn ($q) => $q->latest('id')],
            ['title' => 'پربازدیدترین محصولات', 'viewAllUrl' => '/products?sort=popular', 'query' => fn ($q) => $q->orderByDesc('seen')],
        ];

        return array_values(array_filter(array_map(function (array $row): array {
            $products = Product::query()
                ->published()
                ->with(['featuredImage', 'varieties' => fn ($q) => $q->published()])
                ->tap($row['query'])
                ->limit(self::ROW_LIMIT)
                ->get()
                ->map(fn (Product $product): array => $this->productCard($product))
                ->all();

            return [
                'title' => $row['title'],
                'viewAllUrl' => $row['viewAllUrl'],
                'products' => $products,
            ];
        }, $rows), fn (array $row): bool => $row['products'] !== []));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function brands(): array
    {
        return Brand::query()
            ->active()
            ->with('image')
            ->orderBy('heading')
            ->limit(self::ROW_LIMIT)
            ->get()
            ->map(fn (Brand $brand): array => [
                'id' => $brand->id,
                'heading' => $brand->heading,
                'url' => '/brands/'.$brand->slug,
                'image' => $this->image($brand->image),
            ])
            ->all();
    }

    /**
     * Build a lightweight product card payload, deriving the lowest available
     * price (and sale price) from the product's published varieties.
     *
     * @return array<string, mixed>
     */
    private function productCard(Product $product): array
    {
        /** @var Collection<int, Variety> $varieties */
        $varieties = $product->varieties;

        // Prices are stored as decimals but represent whole Tomans; cast to int.
        $price = (int) ($varieties->min('price') ?? $product->price);

        $rawSalePrice = $varieties
            ->filter(fn ($variety): bool => $variety->sale_price !== null)
            ->min('sale_price');
        $salePrice = $rawSalePrice === null ? null : (int) $rawSalePrice;

        $hasDiscount = $salePrice !== null && $salePrice < $price;

        return [
            'id' => $product->id,
            'heading' => $product->heading,
            'url' => '/products/'.$product->slug,
            'image' => $this->image($product->featuredImage),
            'price' => $price,
            'salePrice' => $hasDiscount ? $salePrice : null,
            'discountPercent' => $hasDiscount ? (int) round((($price - $salePrice) / $price) * 100) : null,
        ];
    }

    /**
     * @return array{url: string, alt: string}|null
     */
    private function image(?Image $image): ?array
    {
        if ($image === null) {
            return null;
        }

        return [
            'url' => $image->url,
            'alt' => (string) ($image->alt_text ?? ''),
        ];
    }
}
