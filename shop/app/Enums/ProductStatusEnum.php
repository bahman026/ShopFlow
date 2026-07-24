<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum ProductStatusEnum: int
{
    use HasOptions;

    case DELETED = 10;
    case PUBLISHED = 20;
    case DRAFT = 30;

    public function label(): string
    {
        return match ($this) {
            self::DELETED => trans('enums.product_status.deleted'),
            self::PUBLISHED => trans('enums.product_status.published'),
            self::DRAFT => trans('enums.product_status.draft'),
        };
    }
}
