<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum TransactionStatusEnum: int
{
    use HasOptions;

    case PENDING = 10;
    case SUCCESS = 20;
    case FAILED = 30;
    case CANCELED = 40;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار',
            self::SUCCESS => 'موفق',
            self::FAILED => 'ناموفق',
            self::CANCELED => 'لغوشده',
        };
    }
}
