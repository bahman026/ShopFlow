<?php

declare(strict_types=1);

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/brands/{slug}', [BrandController::class, 'show'])->name('brands.show');

Route::get('/faq/{position?}', [FaqController::class, 'show'])->name('faqs.show');

Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// CMS pages use clean top-level slugs (e.g. /about-us). Keep this LAST so it
// only matches single-segment paths no other route claimed; unknown slugs 404.
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show');
