<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Models\Variety;
use Illuminate\Support\Collection;

class CalculatePricing
{
    /**
     * Lowest price (and sale price) across a set of varieties, falling back to
     * the product's own price when it has none.
     *
     * @param  Collection<int, Variety>  $varieties
     * @return array{price: int, salePrice: int|null, discountPercent: int|null}
     */
    public function forVarieties(Collection $varieties, int $fallbackPrice): array
    {
        $price = (int) ($varieties->min('price') ?? $fallbackPrice);

        $rawSalePrice = $varieties
            ->filter(fn (Variety $variety): bool => $variety->sale_price !== null)
            ->min('sale_price');

        return $this->shape($price, $rawSalePrice === null ? null : (int) $rawSalePrice);
    }

    /**
     * Pricing for a single variety.
     *
     * @return array{price: int, salePrice: int|null, discountPercent: int|null}
     */
    public function forVariety(Variety $variety): array
    {
        return $this->shape(
            (int) $variety->price,
            $variety->sale_price === null ? null : (int) $variety->sale_price,
        );
    }

    /**
     * @return array{price: int, salePrice: int|null, discountPercent: int|null}
     */
    private function shape(int $price, ?int $salePrice): array
    {
        $hasDiscount = $salePrice !== null && $salePrice < $price;

        return [
            'price' => $price,
            'salePrice' => $hasDiscount ? $salePrice : null,
            'discountPercent' => $hasDiscount ? (int) round((($price - $salePrice) / $price) * 100) : null,
        ];
    }
}
