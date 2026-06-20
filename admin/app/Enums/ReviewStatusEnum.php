<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum ReviewStatusEnum: int
{
    use HasOptions;

    case DELETED = 10;
    case PENDING = 20;
    case APPROVED = 30;
    case REJECTED = 40;

    public function label(): string
    {
        return match ($this) {
            self::DELETED => 'Deleted',
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DELETED => 'danger',
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
