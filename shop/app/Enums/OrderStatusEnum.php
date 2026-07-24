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
            self::PENDING => 'در انتظار پرداخت',
            self::PAID => 'پرداخت‌شده',
            self::PROCESSING => 'در حال آماده‌سازی',
            self::SHIPPED => 'ارسال‌شده',
            self::DELIVERED => 'تحویل داده‌شده',
            self::CANCELED => 'لغوشده',
            self::RETURNED => 'مرجوع‌شده',
        };
    }
}
