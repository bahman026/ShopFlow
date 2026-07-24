<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Home\GetFeaturedBrands;
use App\Actions\Home\GetHomeCategories;
use App\Actions\Home\GetProductRows;
use App\Actions\Home\GetPromoBanners;
use App\Actions\Home\GetSliderByPosition;
use App\Enums\SliderPositionEnum;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        GetSliderByPosition $getSliderByPosition,
        GetHomeCategories $getHomeCategories,
        GetPromoBanners $getPromoBanners,
        GetProductRows $getProductRows,
        GetFeaturedBrands $getFeaturedBrands,
    ): Response {
        return Inertia::render('Home', [
            'slides' => $getSliderByPosition(SliderPositionEnum::HOME_MAIN),
            'categories' => $getHomeCategories(),
            'banners' => $getPromoBanners(),
            'productRows' => $getProductRows(),
            'brands' => $getFeaturedBrands(),
        ]);
    }
}
