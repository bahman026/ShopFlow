<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum SliderPositionEnum: string
{
    use HasOptions;

    case HOME_MAIN = 'home-main';
    case HOME_SECONDARY = 'home-secondary';
    case CATEGORY_TOP = 'category-top';
    case PRODUCT_SIDE = 'product-side';

    public function label(): string
    {
        return match ($this) {
            self::HOME_MAIN => trans('slider.position_home_main'),
            self::HOME_SECONDARY => trans('slider.position_home_secondary'),
            self::CATEGORY_TOP => trans('slider.position_category_top'),
            self::PRODUCT_SIDE => trans('slider.position_product_side'),
        };
    }
}
