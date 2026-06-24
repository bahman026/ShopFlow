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
            self::DELETED => 'حذف شده',
            self::PUBLISHED => 'منتشر شده',
            self::DRAFT => 'پیش‌نویس',
            self::SCHEDULED => 'زمان‌بندی شده',
        };
    }
}
