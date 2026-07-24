<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\Variety;

class AddToCart
{
    /**
     * Add (or increase) a variety line for the given owner. Quantity is clamped
     * to the variety's available inventory; never touches inventory itself.
     *
     * @param  array{user_id: int}|array{session_id: string}  $owner
     */
    public function __invoke(array $owner, Variety $variety, int $count): Cart
    {
        $line = Cart::query()
            ->where($owner)
            ->where('variety_id', $variety->id)
            ->first();

        $current = $line === null ? 0 : $line->count;
        $desired = $current + max(1, $count);
        $clamped = max(1, min($desired, $variety->inventory));

        if ($line === null) {
            return Cart::query()->create([
                ...$owner,
                'variety_id' => $variety->id,
                'count' => $clamped,
            ]);
        }

        $line->update(['count' => $clamped]);

        return $line;
    }
}
