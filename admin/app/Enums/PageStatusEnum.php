<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum PageStatusEnum: int
{
    use HasOptions;

    case DELETED = 10;
    case PUBLISHED = 20;
    case DRAFT = 30;
    case SCHEDULED = 40;

    public function label(): string
    {
        return match ($this) {
            self::DELETED => trans('page.status_deleted'),
            self::PUBLISHED => trans('page.status_published'),
            self::DRAFT => trans('page.status_draft'),
            self::SCHEDULED => trans('page.status_scheduled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DELETED => 'danger',
            self::PUBLISHED => 'success',
            self::DRAFT => 'warning',
            self::SCHEDULED => 'info',
        };
    }
}
