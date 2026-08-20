<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Home\GetFeaturedBrands;
use App\Actions\Home\GetHomeCategories;
use App\Actions\Home\GetProductRows;
use App\Actions\Home\GetTagRows;
use App\Actions\Layout\GetBannersByPosition;
use App\Actions\Layout\GetSliderByPosition;
use App\Enums\BannerPositionEnum;
use App\Enums\SliderPositionEnum;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        GetSliderByPosition $getSliderByPosition,
        GetHomeCategories $getHomeCategories,
        GetBannersByPosition $getBannersByPosition,
        GetProductRows $getProductRows,
        GetTagRows $getTagRows,
        GetFeaturedBrands $getFeaturedBrands,
    ): Response {
        return Inertia::render('Home', [
            // The layout is fixed; each slot is empty until an admin assigns a
            // published banner/slider to that position.
            'topBanners' => $getBannersByPosition(BannerPositionEnum::HOME_TOP),
            'slides' => $getSliderByPosition(SliderPositionEnum::HOME_MAIN),
            'categories' => $getHomeCategories(),
            'banners' => $getBannersByPosition(BannerPositionEnum::HOME_MIDDLE),
            'secondarySlides' => $getSliderByPosition(SliderPositionEnum::HOME_SECONDARY),
            'productRows' => $getProductRows(),
            // One carousel per featured tag, after the standard rows.
            'tagRows' => $getTagRows(),
            'brands' => $getFeaturedBrands(),
        ]);
    }
}
