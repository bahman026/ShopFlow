<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

/**
 * Who a coupon may be used by. ShopFlow is single-vendor, so PARTNERS has no
 * storefront audience — such a coupon is never usable by a customer.
 */
enum CouponForEnum: int
{
    use HasOptions;

    case EVERYONE = 10;
    case USERS = 20;
    case PARTNERS = 30;

    public function label(): string
    {
        return match ($this) {
            self::EVERYONE => trans('enums.coupon_for.everyone'),
            self::USERS => trans('enums.coupon_for.users'),
            self::PARTNERS => trans('enums.coupon_for.partners'),
        };
    }
}
