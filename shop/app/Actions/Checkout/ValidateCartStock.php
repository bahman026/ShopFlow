<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\DTOs\CartLineDTO;
use Illuminate\Support\Collection;

class ValidateCartStock
{
    /**
     * Whether every cart line still has enough live stock to be charged for.
     * Stock can change between adding to cart and reaching payment, so this
     * re-checks live inventory (not just the cart line's own clamped count)
     * right before a Zarinpal payment session is opened — never charge a
     * customer for something that's already out of stock.
     *
     * @param  Collection<int, CartLineDTO>  $lines
     */
    public function __invoke(Collection $lines): bool
    {
        return $lines->every(fn (CartLineDTO $line): bool => $line->inStock && $line->count <= $line->inventory);
    }
}
