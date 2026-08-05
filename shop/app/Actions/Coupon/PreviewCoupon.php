<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\DTOs\CartLineDTO;
use App\DTOs\CouponDTO;
use App\Enums\CouponForEnum;
use App\Enums\CouponStatusEnum;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Check a coupon code against a cart and work out what it would take off.
 *
 * This is a PREVIEW only: nothing is written, `coupons.total_used` is not
 * touched and no order is involved. Committing a coupon to an order belongs to
 * the checkout step (see docs/STOREFRONT_IMPLEMENTATION.md, Phase 4).
 */
class PreviewCoupon
{
    public function __construct(private CalculateCouponDiscount $calculate) {}

    /**
     * @param  Collection<int, CartLineDTO>  $lines
     * @return array{coupon: CouponDTO|null, error: string|null}
     */
    public function __invoke(string $code, ?User $user, Collection $lines): array
    {
        $code = trim($code);

        $coupon = Coupon::query()
            ->with(['products', 'varieties', 'categories'])
            ->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])
            ->first();

        if ($coupon === null) {
            return $this->fail('invalid');
        }

        if ($coupon->status !== CouponStatusEnum::ACTIVE) {
            return $this->fail('inactive');
        }

        if ($coupon->started_at !== null && $coupon->started_at->isFuture()) {
            return $this->fail('not_started');
        }

        if ($coupon->expired_at !== null && $coupon->expired_at->isPast()) {
            return $this->fail('expired');
        }

        if ($coupon->total_uses !== null && $coupon->total_used >= $coupon->total_uses) {
            return $this->fail('exhausted');
        }

        if ($coupon->is_for === CouponForEnum::USERS && $user === null) {
            return $this->fail('login_required');
        }

        // Single-vendor storefront: a partners-only coupon has no audience here.
        if ($coupon->is_for === CouponForEnum::PARTNERS) {
            return $this->fail('not_eligible');
        }

        // A coupon issued to one specific customer.
        if ($coupon->user_id !== null && $coupon->user_id !== $user?->id) {
            return $this->fail('not_eligible');
        }

        if ($lines->isEmpty()) {
            return $this->fail('cart_empty');
        }

        $payable = (int) $lines->sum(fn (CartLineDTO $line): int => $line->lineTotal());

        if ($coupon->min_price !== null && $payable < $coupon->min_price) {
            return $this->fail('min_price');
        }

        $discount = ($this->calculate)($coupon, $lines);

        // A scoped coupon that matches nothing in the cart, or one whose value
        // rounds to nothing, is not "applied" — say so instead of showing a
        // zero saving. Free-shipping coupons are still worth keeping.
        if ($discount === 0 && ! $coupon->shipping) {
            return $this->fail('not_applicable');
        }

        return [
            'coupon' => new CouponDTO(
                code: $coupon->code,
                name: $coupon->name,
                discount: $discount,
                freeShipping: $coupon->shipping,
            ),
            'error' => null,
        ];
    }

    /**
     * @return array{coupon: null, error: string}
     */
    private function fail(string $reason): array
    {
        return [
            'coupon' => null,
            'error' => trans('messages.cart.coupon.'.$reason),
        ];
    }
}
