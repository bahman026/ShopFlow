<?php

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');

// Mobile-first auth: identify by mobile, then verify via OTP or password.
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login/identify', [AuthController::class, 'identify'])->name('login.identify');
    Route::post('/login/otp', [AuthController::class, 'requestOtp'])
        ->middleware('throttle:6,1')
        ->name('login.otp');
    Route::post('/login/otp/verify', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')
        ->name('login.otp.verify');
    Route::post('/login/password', [AuthController::class, 'password'])
        ->middleware('throttle:10,1')
        ->name('login.password');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Customer account area. Dashboard + profile are built; other sidebar links
// render a placeholder for now.
Route::middleware('auth')->prefix('account')->name('account.')->group(function (): void {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/returns', [AccountController::class, 'returns'])->name('returns');
    Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
    Route::get('/reviews', [AccountController::class, 'reviews'])->name('reviews');

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::put('/addresses/{address}/primary', [AddressController::class, 'setPrimary'])->name('addresses.primary');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/addresses-cities', [AddressController::class, 'cities'])->name('addresses.cities');
    Route::get('/addresses-reverse', [AddressController::class, 'reverse'])->name('addresses.reverse');
    Route::get('/addresses-static', [AddressController::class, 'staticMap'])->name('addresses.static');
});

// Cart works for guests (by session) and logged-in users (by user id).
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');

// Checkout requires login; the shipping step collects a delivery address.
Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function (): void {
    Route::get('/', [CheckoutController::class, 'shipping'])->name('shipping');
    Route::get('/methods', [CheckoutController::class, 'methods'])->name('methods');
    Route::post('/shipping', [CheckoutController::class, 'storeShipping'])->name('shipping.store');
    Route::get('/payment', [CheckoutController::class, 'payment'])->name('payment');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/brands/{slug}', [BrandController::class, 'show'])->name('brands.show');

Route::get('/faq/{position?}', [FaqController::class, 'show'])->name('faqs.show');

Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// CMS pages use clean top-level slugs (e.g. /about-us). Keep this LAST so it
// only matches single-segment paths no other route claimed; unknown slugs 404.
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show');
