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
            self::CANCELED => trans('coupon.status_canceled'),
            self::USED => trans('coupon.status_used'),
            self::UNDER_REVIEW => trans('coupon.status_under_review'),
            self::ACTIVE => trans('coupon.status_active'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CANCELED => 'danger',
            self::USED => 'gray',
            self::UNDER_REVIEW => 'warning',
            self::ACTIVE => 'success',
        };
    }
}
