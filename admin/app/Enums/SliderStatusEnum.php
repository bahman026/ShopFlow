<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum SliderStatusEnum: int
{
    use HasOptions;

    case DELETED = 10;
    case PUBLISHED = 20;
    case DRAFT = 30;

    public function label(): string
    {
        return match ($this) {
            self::DELETED => trans('slider.status_deleted'),
            self::PUBLISHED => trans('slider.status_published'),
            self::DRAFT => trans('slider.status_draft'),
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
