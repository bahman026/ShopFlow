<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\Catalog\CalculatePricing;
use App\Actions\Catalog\TransformImage;
use App\Models\Product;
use App\Models\Variety;
use Illuminate\Database\Eloquent\Collection;

class GetRelatedProducts
{
    /**
     * How many related products to show.
     */
    private const LIMIT = 12;

    public function __construct(
        private CalculatePricing $pricing,
        private TransformImage $transformImage,
    ) {}

    /**
     * Related products (lightweight cards) from the same category.
     *
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(Product $product): array
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
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Product $related): array => $this->card($related))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Product $product): array
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
