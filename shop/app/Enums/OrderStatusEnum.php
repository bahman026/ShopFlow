<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum OrderStatusEnum: int
{
    use HasOptions;

    case PENDING = 10;
    case PAID = 20;
    case PROCESSING = 30;
    case SHIPPED = 40;
    case DELIVERED = 50;
    case CANCELED = 60;
    case RETURNED = 70;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => trans('enums.order_status.pending'),
            self::PAID => trans('enums.order_status.paid'),
            self::PROCESSING => trans('enums.order_status.processing'),
            self::SHIPPED => trans('enums.order_status.shipped'),
            self::DELIVERED => trans('enums.order_status.delivered'),
            self::CANCELED => trans('enums.order_status.canceled'),
            self::RETURNED => trans('enums.order_status.returned'),
        };
    }
}
