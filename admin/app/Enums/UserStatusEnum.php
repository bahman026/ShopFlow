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
            self::ACTIVE => trans('user.status_active'),
            self::BLOCK => trans('user.status_block'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::BLOCK => 'danger',
        };
    }
}
