<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\User;

class MergeGuestCart
{
    /**
     * Move a guest session's cart lines onto the user after login. When the
     * same variety already exists for the user, quantities are combined and
     * clamped to the variety's inventory.
     */
    public function __invoke(User $user, ?string $sessionId): void
    {
        if ($sessionId === null || $sessionId === '') {
            return;
        }

        $guestLines = Cart::query()
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('variety')
            ->get();

        foreach ($guestLines as $guestLine) {
            $existing = Cart::query()
                ->where('user_id', $user->id)
                ->where('variety_id', $guestLine->variety_id)
                ->first();

            if ($existing === null) {
                $guestLine->update([
                    'user_id' => $user->id,
                    'session_id' => null,
                ]);

                continue;
            }

            $cap = $guestLine->variety->inventory;
            $existing->update([
                'count' => max(1, min($existing->count + $guestLine->count, $cap)),
            ]);
            $guestLine->delete();
        }
    }
}
