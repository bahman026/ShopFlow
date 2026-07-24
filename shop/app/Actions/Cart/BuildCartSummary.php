<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DTOs\CartLineDTO;
use App\DTOs\CartSummaryDTO;
use Illuminate\Support\Collection;

class BuildCartSummary
{
    /**
     * Totals for a set of cart lines. Discount is the saving from variety sale
     * prices; coupons are not applied here.
     *
     * @param  Collection<int, CartLineDTO>  $lines
     */
    public function __invoke(Collection $lines): CartSummaryDTO
    {
        $itemsTotal = (int) $lines->sum(fn (CartLineDTO $line): int => $line->lineOriginalTotal());
        $payable = (int) $lines->sum(fn (CartLineDTO $line): int => $line->lineTotal());

        return new CartSummaryDTO(
            count: (int) $lines->sum(fn (CartLineDTO $line): int => $line->count),
            itemsTotal: $itemsTotal,
            discount: $itemsTotal - $payable,
            payable: $payable,
        );
    }
}
