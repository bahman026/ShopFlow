<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum OrderShippingPaymentTypeEnum: int
{
    use HasOptions;

    case CASH = 10;
    case POS = 20;
    case ONLINE = 30;

    public function label(): string
    {
        return match ($this) {
            self::CASH => trans('order_shipping.payment_cash'),
            self::POS => trans('order_shipping.payment_pos'),
            self::ONLINE => trans('order_shipping.payment_online'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CASH => 'warning',
            self::POS => 'info',
            self::ONLINE => 'success',
        };
    }
}
