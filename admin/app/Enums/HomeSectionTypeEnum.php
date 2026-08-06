<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

/**
 * The kinds of section the storefront home page can render. Each type maps to
 * one storefront component + data action; the admin arranges an ordered list
 * of these (home_sections table) to compose the home page.
 */
enum HomeSectionTypeEnum: string
{
    use HasOptions;

    case SLIDER = 'slider';
    case TAGS = 'tags';
    case CATEGORIES = 'categories';
    case BANNERS = 'banners';
    case PRODUCTS = 'products';
    case BRANDS = 'brands';

    public function label(): string
    {
        return match ($this) {
            self::SLIDER => trans('home_section.type_slider'),
            self::TAGS => trans('home_section.type_tags'),
            self::CATEGORIES => trans('home_section.type_categories'),
            self::BANNERS => trans('home_section.type_banners'),
            self::PRODUCTS => trans('home_section.type_products'),
            self::BRANDS => trans('home_section.type_brands'),
        };
    }
}
