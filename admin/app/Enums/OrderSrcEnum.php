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
            self::PWA => trans('order.src_pwa'),
            self::WEB => trans('order.src_web'),
            self::APP => trans('order.src_app'),
            self::OLD => trans('order.src_old'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PWA => 'info',
            self::WEB => 'success',
            self::APP => 'primary',
            self::OLD => 'gray',
        };
    }
}
