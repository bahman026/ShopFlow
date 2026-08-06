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
            self::DELETED => trans('enums.page_status.deleted'),
            self::PUBLISHED => trans('enums.page_status.published'),
            self::DRAFT => trans('enums.page_status.draft'),
            self::SCHEDULED => trans('enums.page_status.scheduled'),
        };
    }
}
