<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum ShippingMethodForEnum: int
{
    use HasOptions;

    case CUSTOMER = 10;
    case PARTNER = 20;
    case EMPLOYEE = 30;

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Customer',
            self::PARTNER => 'Partner',
            self::EMPLOYEE => 'Employee',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CUSTOMER => 'success',
            self::PARTNER => 'info',
            self::EMPLOYEE => 'warning',
        };
    }
}
