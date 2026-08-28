<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\DTOs\CartLineDTO;
use App\DTOs\CouponDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ResolveCartCoupon
{
    /**
     * Session key holding the discount code the customer applied on the cart.
     *
     * The code lives in the session rather than on the cart row so it survives
     * the cart changing underneath it; it is re-validated on every read.
     */
    public const SESSION_KEY = 'cart_coupon_code';

    public function __construct(private PreviewCoupon $preview) {}

    /**
     * Re-validate the applied code against the cart as it stands right now.
     *
     * Every page that shows or charges a total goes through here, so the cart,
     * the payment page and the amount actually charged cannot disagree about
     * the discount. A coupon that has since expired, run out of uses, or no
     * longer matches the cart comes back as null with the reason.
     *
     * @param  Collection<int, CartLineDTO>  $lines
     * @return array{coupon: CouponDTO|null, error: string|null}
     */
    public function __invoke(Request $request, Collection $lines): array
    {
        $code = $request->session()->get(self::SESSION_KEY);

        if (! is_string($code) || $code === '') {
            return ['coupon' => null, 'error' => null];
        }

        $user = $request->user();

        return ($this->preview)($code, $user instanceof User ? $user : null, $lines);
    }

    /**
     * Whether a code is currently applied, regardless of whether it still
     * validates. Lets a caller tell "no coupon" apart from "a coupon that has
     * just stopped working", which are very different things to charge on.
     */
    public function isApplied(Request $request): bool
    {
        $code = $request->session()->get(self::SESSION_KEY);

        return is_string($code) && $code !== '';
    }

    public function remember(Request $request, string $code): void
    {
        $request->session()->put(self::SESSION_KEY, $code);
    }

    public function forget(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }
}
