<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum DiscountForEnum: int
{
    use HasOptions;

    case EVERYONE = 10;
    case USERS = 20;
    case PARTNERS = 30;

    public function label(): string
    {
        return match ($this) {
            self::EVERYONE => trans('discount.for_everyone'),
            self::USERS => trans('discount.for_users'),
            self::PARTNERS => trans('discount.for_partners'),
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
