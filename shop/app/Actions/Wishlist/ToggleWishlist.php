<?php

declare(strict_types=1);

namespace App\Actions\Wishlist;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;

class ToggleWishlist
{
    /**
     * Add the product to the user's wishlist, or remove it if already saved.
     *
     * @return bool true if the product is now wishlisted, false if it was just removed
     */
    public function __invoke(User $user, Product $product): bool
    {
        $existing = Wishlist::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return false;
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return true;
    }
}
