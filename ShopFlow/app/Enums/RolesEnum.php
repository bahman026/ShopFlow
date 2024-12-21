<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum RolesEnum: string
{
    use HasOptions;

    case USER = 'user';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super-admin';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'User',
            self::ADMIN => 'Admin',
            self::SUPER_ADMIN => 'Super Admin',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::USER => 'info',
            self::ADMIN => 'success',
            self::SUPER_ADMIN => 'primary',
        };
    }
}
