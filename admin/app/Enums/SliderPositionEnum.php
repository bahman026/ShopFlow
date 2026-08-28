<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasDescriptions;
use App\Traits\HasOptions;

/**
 * Where a slider is shown on the storefront. The storefront mirrors this enum
 * (same string values) and has a render site for every case, so anything
 * chosen here appears on the site once the slider is published and has slides.
 */
enum SliderPositionEnum: string
{
    use HasDescriptions;
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

    /**
     * One sentence describing exactly where on the page this lands, shown
     * under the option in the admin form.
     */
    public function description(): string
    {
        return match ($this) {
            self::HOME_MAIN => trans('slider.position_home_main_description'),
            self::HOME_SECONDARY => trans('slider.position_home_secondary_description'),
            self::CATEGORY_TOP => trans('slider.position_category_top_description'),
            self::PRODUCT_SIDE => trans('slider.position_product_side_description'),
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
            self::HOME_MAIN => '3:1',
            self::HOME_SECONDARY => '4:1',
            self::CATEGORY_TOP => '4:1',
            self::PRODUCT_SIDE => '4:5',
        };
    }

    /**
     * Recommended source dimensions in pixels — the rendered size at roughly
     * 2x, so the image stays sharp on a retina screen without being wasteful.
     */
    public function recommendedSize(): string
    {
        return match ($this) {
            self::HOME_MAIN => '1920 × 640',
            self::HOME_SECONDARY => '1920 × 480',
            self::CATEGORY_TOP => '1920 × 480',
            self::PRODUCT_SIDE => '600 × 750',
        };
    }

    /**
     * Which storefront page this position sits on — drives the wireframe the
     * admin form highlights.
     */
    public function page(): string
    {
        return match ($this) {
            self::HOME_MAIN, self::HOME_SECONDARY => 'home',
            self::CATEGORY_TOP => 'category',
            self::PRODUCT_SIDE => 'product',
        };
    }
}
