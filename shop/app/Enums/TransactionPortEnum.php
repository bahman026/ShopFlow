<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum TransactionPortEnum: int
{
    use HasOptions;

    case MELLAT = 10;
    case PARSIAN = 20;
    case ZARINPAL = 30;

    public function label(): string
    {
        return match ($this) {
            self::MELLAT => 'ملت',
            self::PARSIAN => 'پارسیان',
            self::ZARINPAL => 'زرین‌پال',
        };
    }
}
