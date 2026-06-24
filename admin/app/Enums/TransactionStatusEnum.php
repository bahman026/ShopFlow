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
            self::PENDING => trans('transaction.status_pending'),
            self::SUCCESS => trans('transaction.status_success'),
            self::FAILED => trans('transaction.status_failed'),
            self::CANCELED => trans('transaction.status_canceled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::SUCCESS => 'success',
            self::FAILED => 'danger',
            self::CANCELED => 'gray',
        };
    }
}
