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
            self::DELETED => trans('review.status_deleted'),
            self::PENDING => trans('review.status_pending'),
            self::APPROVED => trans('review.status_approved'),
            self::REJECTED => trans('review.status_rejected'),
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
