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
            self::PENDING => trans('enums.transaction_status.pending'),
            self::SUCCESS => trans('enums.transaction_status.success'),
            self::FAILED => trans('enums.transaction_status.failed'),
            self::CANCELED => trans('enums.transaction_status.canceled'),
        };
    }
}
