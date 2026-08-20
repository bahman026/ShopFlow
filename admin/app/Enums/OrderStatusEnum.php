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
            self::PENDING => trans('order.status_pending'),
            self::PAID => trans('order.status_paid'),
            self::PROCESSING => trans('order.status_processing'),
            self::SHIPPED => trans('order.status_shipped'),
            self::DELIVERED => trans('order.status_delivered'),
            self::CANCELED => trans('order.status_canceled'),
            self::RETURNED => trans('order.status_returned'),
        };
    }

    /**
     * Whether an order in this status is holding stock.
     *
     * This is the single definition of the invariant docs/ORDER.md states:
     * stock is consumed from the moment an order is paid and released again if
     * it is canceled or returned. Everything that moves an order between these
     * two sets has to keep `varieties.inventory` in step (OrderObserver).
     */
    public function consumesStock(): bool
    {
        return match ($this) {
            self::PAID, self::PROCESSING, self::SHIPPED, self::DELIVERED => true,
            self::PENDING, self::CANCELED, self::RETURNED => false,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID => 'info',
            self::PROCESSING => 'info',
            self::SHIPPED => 'info',
            self::DELIVERED => 'success',
            self::CANCELED => 'danger',
            self::RETURNED => 'danger',
        };
    }
}
