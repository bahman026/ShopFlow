<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum UserStatusEnum: int
{
    use HasOptions;

    case ACTIVE = 1;
    case BLOCK = 2;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => trans('enums.user_status.active'),
            self::BLOCK => trans('enums.user_status.block'),
        };
    }
}
