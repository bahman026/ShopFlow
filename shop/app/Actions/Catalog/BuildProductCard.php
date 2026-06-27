<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Models\Product;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Collection;

class BuildProductCard
{
    public function __construct(
        private CalculatePricing $pricing,
        private TransformImage $transformImage,
    ) {}

    /**
     * Lightweight product card payload (used by carousels and related lists),
     * deriving the lowest available price from the product's varieties.
     *
     * @return array<string, mixed>
     */
    public function __invoke(Product $product): array
    {
        /** @var Collection<int, Variety> $varieties */
        $varieties = $product->varieties;

        $pricing = $this->pricing->forVarieties($varieties, (int) $product->price);

        return [
            'id' => $product->id,
            'heading' => $product->heading,
            'url' => '/products/'.$product->slug,
            'image' => ($this->transformImage)($product->featuredImage)?->toArray(),
            'price' => $pricing['price'],
            'salePrice' => $pricing['salePrice'],
            'discountPercent' => $pricing['discountPercent'],
        ];
    }
}
