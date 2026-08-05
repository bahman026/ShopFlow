<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Home\GetBannersByPosition;
use App\Actions\Home\GetFeaturedBrands;
use App\Actions\Home\GetHomeCategories;
use App\Actions\Home\GetHomeTags;
use App\Actions\Home\GetProductRows;
use App\Actions\Home\GetSliderByPosition;
use App\Enums\BannerPositionEnum;
use App\Enums\SliderPositionEnum;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        GetSliderByPosition $getSliderByPosition,
        GetHomeCategories $getHomeCategories,
        GetHomeTags $getHomeTags,
        GetBannersByPosition $getBannersByPosition,
        GetProductRows $getProductRows,
        GetFeaturedBrands $getFeaturedBrands,
    ): Response {
        return Inertia::render('Home', [
            'slides' => $getSliderByPosition(SliderPositionEnum::HOME_MAIN),
            'categories' => $getHomeCategories(),
            'tags' => $getHomeTags(),
            'banners' => $getBannersByPosition(BannerPositionEnum::HOME_MIDDLE),
            'productRows' => $getProductRows(),
            'brands' => $getFeaturedBrands(),
        ]);
    }
}
