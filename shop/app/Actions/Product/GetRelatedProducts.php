<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\Catalog\BuildProductCard;
use App\Enums\VarietyStatusEnum;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\Relation;

class GetRelatedProducts
{
    /**
     * How many related products to show.
     */
    private const LIMIT = 12;

    public function __construct(private BuildProductCard $buildProductCard) {}

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
            ->with(['featuredImage', 'varieties' => fn (Relation $query) => $query->where('status', VarietyStatusEnum::PUBLISHED->value)->with('image')])
            ->latest('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Product $related): array => ($this->buildProductCard)($related))
            ->all();
    }
}
