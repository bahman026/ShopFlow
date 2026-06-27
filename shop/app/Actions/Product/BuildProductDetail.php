<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\Catalog\CalculatePricing;
use App\Actions\Catalog\TransformImage;
use App\DTOs\ImageDTO;
use App\DTOs\ProductDTO;
use App\DTOs\ReviewDTO;
use App\DTOs\VarietyDTO;
use App\Models\Attribute;
use App\Models\Image;
use App\Models\Product;
use App\Models\Review;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Collection;

class BuildProductDetail
{
    public function __construct(
        private CalculatePricing $pricing,
        private TransformImage $transformImage,
        private BuildVariantAxes $buildVariantAxes,
        private VarietyAttributes $varietyAttributes,
    ) {}

    /**
     * Build the full product detail DTO for the storefront page.
     */
    public function __invoke(Product $product): ProductDTO
    {
        /** @var Collection<int, Variety> $varieties */
        $varieties = $product->varieties;

        $pricing = $this->pricing->forVarieties($varieties, (int) $product->price);

        return new ProductDTO(
            id: $product->id,
            heading: $product->heading,
            url: '/products/'.$product->slug,
            content: $product->content,
            title: $product->title,
            description: $product->description,
            noIndex: (bool) $product->no_index,
            canonical: $product->canonical,
            image: ($this->transformImage)($product->featuredImage),
            gallery: $this->gallery($product),
            brand: $product->brand === null ? null : [
                'heading' => $product->brand->heading,
                'url' => '/brands/'.$product->brand->slug,
            ],
            category: $product->category === null ? null : [
                'heading' => $product->category->heading,
                'url' => '/categories/'.$product->category->slug,
            ],
            price: $pricing['price'],
            salePrice: $pricing['salePrice'],
            discountPercent: $pricing['discountPercent'],
            inStock: $varieties->contains(fn (Variety $variety): bool => $this->inStock($variety)),
            variantAxes: ($this->buildVariantAxes)($varieties),
            varieties: $varieties->map(fn (Variety $variety): VarietyDTO => $this->variety($variety))->all(),
            highlights: $product->attributes
                ->filter(fn (Attribute $attribute): bool => (bool) ($attribute->pivot->is_highlight ?? false))
                ->map(fn (Attribute $attribute): array => ['value' => $attribute->value])
                ->values()
                ->all(),
            specs: $product->attributes
                ->map(fn (Attribute $attribute): array => ['value' => $attribute->value])
                ->all(),
            reviews: $product->reviews
                ->map(fn (Review $review): ReviewDTO => new ReviewDTO(
                    id: $review->id,
                    heading: $review->heading,
                    content: $review->content,
                    author: $review->user?->name,
                    date: $review->created_at?->toIso8601String(),
                ))
                ->all(),
            reviewCount: $product->reviews->count(),
        );
    }

    private function variety(Variety $variety): VarietyDTO
    {
        $pricing = $this->pricing->forVariety($variety);
        $inStock = $this->inStock($variety);

        return new VarietyDTO(
            id: $variety->id,
            label: $variety->attribute_value ?? $variety->attribute?->value,
            color: $variety->color,
            price: $pricing['price'],
            salePrice: $pricing['salePrice'],
            discountPercent: $pricing['discountPercent'],
            inStock: $inStock,
            inventory: $inStock ? (int) $variety->inventory : 0,
            image: ($this->transformImage)($variety->image),
            options: $this->options($variety),
        );
    }

    /**
     * Map of attribute-group id => list of values for a variety. A variety can
     * carry several values in the same group (e.g. a color offered in sizes).
     *
     * @return array<int, array<int, string>>
     */
    private function options(Variety $variety): array
    {
        $options = [];

        foreach (($this->varietyAttributes)($variety) as $attribute) {
            $options[$attribute->attribute_group_id][] = $attribute->value;
        }

        return array_map(
            fn (array $values): array => array_values(array_unique($values)),
            $options,
        );
    }

    /**
     * Featured image first, then the rest of the gallery.
     *
     * @return array<int, ImageDTO>
     */
    private function gallery(Product $product): array
    {
        return $product->images
            ->sortByDesc('is_featured')
            ->map(fn (Image $image): ImageDTO => new ImageDTO(
                url: $image->url,
                alt: (string) ($image->alt_text ?? $product->heading),
            ))
            ->values()
            ->all();
    }

    private function inStock(Variety $variety): bool
    {
        return $variety->has_stock && $variety->inventory > 0;
    }
}
