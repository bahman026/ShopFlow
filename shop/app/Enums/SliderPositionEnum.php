<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

/**
 * Fixed set of places a slider can be shown on the storefront. Mirrors the
 * admin enum (same string values) so the position an admin assigns and the
 * position the frontend looks up can never disagree.
 *
 * Every case has a render site: HOME_MAIN and HOME_SECONDARY on the home page,
 * CATEGORY_TOP on the category listing, PRODUCT_SIDE beside the buy box. Each
 * renders nothing until a published slider is assigned to it.
 */
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
            self::HOME_MAIN => trans('enums.slider_position.home_main'),
            self::HOME_SECONDARY => trans('enums.slider_position.home_secondary'),
            self::CATEGORY_TOP => trans('enums.slider_position.category_top'),
            self::PRODUCT_SIDE => trans('enums.slider_position.product_side'),
        };
    }
}
