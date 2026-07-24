<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Wishlist\ToggleWishlist;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Request $request, Product $product, ToggleWishlist $toggle): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $wishlisted = $toggle($user, $product);

        return back()->with('status', trans($wishlisted ? 'messages.wishlist.added' : 'messages.wishlist.removed'));
    }
}
