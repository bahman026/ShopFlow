<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Models\User;
use Illuminate\Http\Request;

class ResolveCartOwner
{
    /**
     * The column/value pair that identifies the current cart: the user id when
     * logged in, otherwise the guest session id.
     *
     * @return array{user_id: int}|array{session_id: string}
     */
    public function __invoke(Request $request): array
    {
        $user = $request->user();

        if ($user instanceof User) {
            return ['user_id' => $user->id];
        }

        return ['session_id' => $request->session()->getId()];
    }
}
