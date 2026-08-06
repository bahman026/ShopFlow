<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Cart\MergeGuestCart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginUser
{
    public function __construct(private MergeGuestCart $mergeGuestCart) {}

    /**
     * Log a user in and carry over anything they built as a guest.
     *
     * The guest session id is captured BEFORE the session is regenerated, so a
     * cart filled while logged out is merged onto the account instead of being
     * orphaned. Shared by every entry point that authenticates someone (OTP,
     * password, password reset).
     */
    public function __invoke(Request $request, User $user): void
    {
        $guestSession = $request->session()->getId();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        ($this->mergeGuestCart)($user, $guestSession);
    }
}
