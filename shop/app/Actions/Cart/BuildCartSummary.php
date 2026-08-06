<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DTOs\CartLineDTO;
use App\DTOs\CartSummaryDTO;
use Illuminate\Support\Collection;

class BuildCartSummary
{
    /**
     * Totals for a set of cart lines. `discount` is the saving from variety
     * sale prices; a coupon saving is passed in separately (the cart page
     * previews one, checkout does not yet apply one) and shown as its own line
     * so the two never blur together.
     *
     * @param  Collection<int, CartLineDTO>  $lines
     */
    public function __invoke(Collection $lines, int $couponDiscount = 0): CartSummaryDTO
    {
        $itemsTotal = (int) $lines->sum(fn (CartLineDTO $line): int => $line->lineOriginalTotal());
        $payable = (int) $lines->sum(fn (CartLineDTO $line): int => $line->lineTotal());
        $couponDiscount = max(0, min($couponDiscount, $payable));

        return new CartSummaryDTO(
            count: (int) $lines->sum(fn (CartLineDTO $line): int => $line->count),
            itemsTotal: $itemsTotal,
            discount: $itemsTotal - $payable,
            payable: $payable - $couponDiscount,
            couponDiscount: $couponDiscount,
        );
    }
}
