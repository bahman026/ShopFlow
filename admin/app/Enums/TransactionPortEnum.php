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
            self::MELLAT => trans('transaction.port_mellat'),
            self::PARSIAN => trans('transaction.port_parsian'),
            self::ZARINPAL => trans('transaction.port_zarinpal'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MELLAT => 'info',
            self::PARSIAN => 'warning',
            self::ZARINPAL => 'success',
        };
    }
}
