<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum GatewayForEnum: int
{
    use HasOptions;

    case EVERYONE = 10;
    case USERS = 20;
    case PARTNERS = 30;

    public function label(): string
    {
        return match ($this) {
            self::EVERYONE => trans('gateway.for_everyone'),
            self::USERS => trans('gateway.for_users'),
            self::PARTNERS => trans('gateway.for_partners'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EVERYONE => 'info',
            self::USERS => 'success',
            self::PARTNERS => 'warning',
        };
    }
}
