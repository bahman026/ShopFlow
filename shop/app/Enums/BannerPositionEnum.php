<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

/**
 * Fixed set of places a banner can be shown on the storefront. Mirrors the
 * admin enum (same string values) so the position an admin assigns and the
 * position the frontend looks up can never disagree.
 *
 * Every case has a render site: HOME_TOP and HOME_MIDDLE on the home page,
 * CATEGORY_SIDE on the category listing. Each renders nothing until a
 * published banner is assigned to it.
 */
enum BannerPositionEnum: string
{
    use HasOptions;

    case HOME_TOP = 'home-top';
    case HOME_MIDDLE = 'home-middle';
    case CATEGORY_SIDE = 'category-side';

    public function label(): string
    {
        return match ($this) {
            self::HOME_TOP => trans('enums.banner_position.home_top'),
            self::HOME_MIDDLE => trans('enums.banner_position.home_middle'),
            self::CATEGORY_SIDE => trans('enums.banner_position.category_side'),
        };
    }
}
