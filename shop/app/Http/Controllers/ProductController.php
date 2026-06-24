<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Review;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * How many related products to show.
     */
    private const RELATED_LIMIT = 12;

    public function show(string $slug): Response
    {
        $product = Product::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'featuredImage',
                'images',
                'brand',
                'category',
                'varieties' => fn ($query) => $query->published()->with(['image', 'attribute']),
                'attributes',
                'reviews' => fn ($query) => $query->approved()->whereNull('parent_id')->with('user')->latest(),
            ])
            ->firstOrFail();

        $product->increment('seen');

        return Inertia::render('Product/Show', [
            'product' => $this->productPayload($product),
            'breadcrumbs' => $this->breadcrumbs($product),
            'related' => $this->related($product),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function productPayload(Product $product): array
    {
        /** @var Collection<int, Variety> $varieties */
        $varieties = $product->varieties;

        $price = (int) ($varieties->min('price') ?? $product->price);

        $rawSalePrice = $varieties
            ->filter(fn (Variety $variety): bool => $variety->sale_price !== null)
            ->min('sale_price');
        $salePrice = $rawSalePrice === null ? null : (int) $rawSalePrice;
        $hasDiscount = $salePrice !== null && $salePrice < $price;

        return [
            'id' => $product->id,
            'heading' => $product->heading,
            'url' => '/products/'.$product->slug,
            'content' => $product->content,
            'title' => $product->title,
            'description' => $product->description,
            'noIndex' => $product->no_index,
            'canonical' => $product->canonical,
            'image' => $this->image($product->featuredImage),
            'gallery' => $this->gallery($product),
            'brand' => $product->brand === null ? null : [
                'heading' => $product->brand->heading,
                'url' => '/brands/'.$product->brand->slug,
            ],
            'category' => $product->category === null ? null : [
                'heading' => $product->category->heading,
                'url' => '/categories/'.$product->category->slug,
            ],
            'price' => $price,
            'salePrice' => $hasDiscount ? $salePrice : null,
            'discountPercent' => $hasDiscount ? $this->discountPercent($price, $salePrice) : null,
            'inStock' => $varieties->contains(fn (Variety $variety): bool => $this->varietyInStock($variety)),
            'varieties' => $varieties->map(fn (Variety $variety): array => $this->varietyPayload($variety))->all(),
            'highlights' => $product->attributes
                ->filter(fn ($attribute): bool => (bool) ($attribute->pivot->is_highlight ?? false))
                ->map(fn ($attribute): array => ['value' => $attribute->value])
                ->values()
                ->all(),
            'specs' => $product->attributes
                ->map(fn ($attribute): array => ['value' => $attribute->value])
                ->all(),
            'reviews' => $product->reviews
                ->map(fn (Review $review): array => [
                    'id' => $review->id,
                    'heading' => $review->heading,
                    'content' => $review->content,
                    'author' => $review->user?->name,
                    'date' => $review->created_at?->toIso8601String(),
                ])
                ->all(),
            'reviewCount' => $product->reviews->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function varietyPayload(Variety $variety): array
    {
        $price = (int) $variety->price;
        $salePrice = $variety->sale_price === null ? null : (int) $variety->sale_price;
        $hasDiscount = $salePrice !== null && $salePrice < $price;

        return [
            'id' => $variety->id,
            'label' => $variety->attribute_value ?? $variety->attribute?->value,
            'color' => $variety->color,
            'price' => $price,
            'salePrice' => $hasDiscount ? $salePrice : null,
            'discountPercent' => $hasDiscount ? $this->discountPercent($price, $salePrice) : null,
            'inStock' => $this->varietyInStock($variety),
            'image' => $this->image($variety->image),
        ];
    }

    /**
     * Featured image first, then the rest of the gallery.
     *
     * @return array<int, array{url: string, alt: string}>
     */
    private function gallery(Product $product): array
    {
        return $product->images
            ->sortByDesc('is_featured')
            ->map(fn (Image $image): array => [
                'url' => $image->url,
                'alt' => (string) ($image->alt_text ?? $product->heading),
            ])
            ->values()
            ->all();
    }

    /**
     * Breadcrumb trail: home, category ancestors, product.
     *
     * @return array<int, array{heading: string, url: string|null}>
     */
    private function breadcrumbs(Product $product): array
    {
        $trail = [['heading' => 'خانه', 'url' => '/']];

        $chain = [];
        $category = $product->category;
        while ($category instanceof Category) {
            array_unshift($chain, [
                'heading' => $category->heading,
                'url' => '/categories/'.$category->slug,
            ]);
            $category = $category->parent;
        }

        $trail = array_merge($trail, $chain);
        $trail[] = ['heading' => $product->heading, 'url' => null];

        return $trail;
    }

    /**
     * Related products from the same category.
     *
     * @return array<int, array<string, mixed>>
     */
    private function related(Product $product): array
    {
        if ($product->category_id === null) {
            return [];
        }

        return Product::query()
            ->published()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['featuredImage', 'varieties' => fn ($query) => $query->published()])
            ->latest('id')
            ->limit(self::RELATED_LIMIT)
            ->get()
            ->map(fn (Product $related): array => $this->relatedCard($related))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function relatedCard(Product $product): array
    {
        /** @var Collection<int, Variety> $varieties */
        $varieties = $product->varieties;

        $price = (int) ($varieties->min('price') ?? $product->price);
        $rawSalePrice = $varieties
            ->filter(fn (Variety $variety): bool => $variety->sale_price !== null)
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
            'discountPercent' => $hasDiscount ? $this->discountPercent($price, $salePrice) : null,
        ];
    }

    private function varietyInStock(Variety $variety): bool
    {
        return $variety->has_stock && $variety->inventory > 0;
    }

    private function discountPercent(int $price, int $salePrice): int
    {
        return (int) round((($price - $salePrice) / $price) * 100);
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
