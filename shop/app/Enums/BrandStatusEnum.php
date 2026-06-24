<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum BrandStatusEnum: int
{
    use HasOptions;

    case ACTIVE = 1;
    case INACTIVE = 2;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::INACTIVE => 'غیرفعال',
        };
    }
}
