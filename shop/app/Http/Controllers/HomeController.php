<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Home\GetFeaturedBrands;
use App\Actions\Home\GetHeroSlides;
use App\Actions\Home\GetHomeCategories;
use App\Actions\Home\GetProductRows;
use App\Actions\Home\GetPromoBanners;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        GetHeroSlides $getHeroSlides,
        GetHomeCategories $getHomeCategories,
        GetPromoBanners $getPromoBanners,
        GetProductRows $getProductRows,
        GetFeaturedBrands $getFeaturedBrands,
    ): Response {
        return Inertia::render('Home', [
            'slides' => $getHeroSlides(),
            'categories' => $getHomeCategories(),
            'banners' => $getPromoBanners(),
            'productRows' => $getProductRows(),
            'brands' => $getFeaturedBrands(),
        ]);
    }
}
