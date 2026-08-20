<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasDescriptions;
use App\Traits\HasOptions;

/**
 * Where a banner is shown on the storefront. The storefront mirrors this enum
 * (same string values) and has a render site for every case, so anything
 * chosen here appears on the site once the banner is published.
 */
enum BannerPositionEnum: string
{
    use HasDescriptions;
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

    /**
     * One sentence describing exactly where on the page this lands, shown
     * under the option in the admin form.
     */
    public function description(): string
    {
        return match ($this) {
            self::HOME_TOP => trans('banner.position_home_top_description'),
            self::HOME_MIDDLE => trans('banner.position_home_middle_description'),
            self::CATEGORY_SIDE => trans('banner.position_category_side_description'),
        };
    }

    /**
     * The aspect ratio this slot renders at on the storefront, as Filament's
     * crop format. The upload is cropped to it so a stray tall or square image
     * cannot stretch the layout.
     */
    public function aspectRatio(): string
    {
        return match ($this) {
            self::HOME_TOP => '5:1',
            self::HOME_MIDDLE => '16:9',
            self::CATEGORY_SIDE => '4:5',
        };
    }

    /**
     * Recommended source dimensions in pixels — the rendered size at roughly
     * 2x, so the image stays sharp on a retina screen without being wasteful.
     */
    public function recommendedSize(): string
    {
        return match ($this) {
            self::HOME_TOP => '1920 × 384',
            self::HOME_MIDDLE => '800 × 450',
            self::CATEGORY_SIDE => '600 × 750',
        };
    }

    /**
     * Which storefront page this position sits on — drives the wireframe the
     * admin form highlights.
     */
    public function page(): string
    {
        return match ($this) {
            self::HOME_TOP, self::HOME_MIDDLE => 'home',
            self::CATEGORY_SIDE => 'category',
        };
    }
}
