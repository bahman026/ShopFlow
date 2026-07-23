<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum OrderSrcEnum: int
{
    use HasOptions;

    case PWA = 1;
    case WEB = 2;
    case APP = 3;
    case OLD = 4;

    public function label(): string
    {
        return match ($this) {
            self::PWA => 'اپلیکیشن وب',
            self::WEB => 'وبسایت',
            self::APP => 'اپلیکیشن موبایل',
            self::OLD => 'سامانه قدیم',
        };
    }
}
