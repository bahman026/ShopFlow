<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum BannerPositionEnum: string
{
    use HasOptions;

    case HOME_TOP = 'home-top';
    case HOME_MIDDLE = 'home-middle';
    case CATEGORY_SIDE = 'category-side';

    public function label(): string
    {
        return match ($this) {
            self::HOME_TOP => trans('banner.position_home_top'),
            self::HOME_MIDDLE => trans('banner.position_home_middle'),
            self::CATEGORY_SIDE => trans('banner.position_category_side'),
        };
    }
}
