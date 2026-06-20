<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum BannerStatusEnum: int
{
    use HasOptions;

    case DELETED = 10;
    case PUBLISHED = 20;
    case DRAFT = 30;

    public function label(): string
    {
        return match ($this) {
            self::DELETED => trans('banner.status_deleted'),
            self::PUBLISHED => trans('banner.status_published'),
            self::DRAFT => trans('banner.status_draft'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DELETED => 'danger',
            self::PUBLISHED => 'success',
            self::DRAFT => 'warning',
        };
    }
}
