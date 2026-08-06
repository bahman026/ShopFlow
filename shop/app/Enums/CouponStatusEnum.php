<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum CouponStatusEnum: int
{
    use HasOptions;

    case CANCELED = 10;
    case USED = 20;
    case UNDER_REVIEW = 30;
    case ACTIVE = 40;

    public function label(): string
    {
        return match ($this) {
            self::CANCELED => trans('enums.coupon_status.canceled'),
            self::USED => trans('enums.coupon_status.used'),
            self::UNDER_REVIEW => trans('enums.coupon_status.under_review'),
            self::ACTIVE => trans('enums.coupon_status.active'),
        };
    }
}
