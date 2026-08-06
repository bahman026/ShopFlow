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
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WishlistController;
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

    // Password reset over two channels: a one-time code to the account's
    // mobile, or a signed link to its email (Laravel's password broker).
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password/otp', [PasswordResetController::class, 'sendOtp'])
        ->middleware('throttle:6,1')
        ->name('password.otp');
    Route::post('/forgot-password/otp/resend', [PasswordResetController::class, 'resendOtp'])
        ->middleware('throttle:6,1')
        ->name('password.otp.resend');
    Route::post('/forgot-password/otp/verify', [PasswordResetController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')
        ->name('password.otp.verify');
    Route::post('/forgot-password/mobile', [PasswordResetController::class, 'updateWithMobile'])
        ->middleware('throttle:10,1')
        ->name('password.mobile.update');
    Route::post('/forgot-password/email', [PasswordResetController::class, 'sendEmailLink'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Customer account area. Dashboard, profile, addresses, orders, returns and
// wishlist are built; other sidebar links render a placeholder for now.
Route::middleware('auth')->prefix('account')->name('account.')->group(function (): void {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [AccountController::class, 'showOrder'])->name('orders.show');
    Route::get('/orders/{order}/receipt', [AccountController::class, 'receipt'])->name('orders.receipt');
    Route::post('/orders/{order}/retry', [AccountController::class, 'retryOrder'])->name('orders.retry');
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

// Coupon preview only — the code is held in the session and shown as a saving
// on the cart; committing it to an order is checkout work (Phase 4). Declared
// before the `{cart}` routes (and those constrained to numbers) so /cart/coupon
// is never mistaken for a cart-line id.
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

Route::patch('/cart/{cart}', [CartController::class, 'update'])->whereNumber('cart')->name('cart.update');
Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->whereNumber('cart')->name('cart.destroy');

// Checkout requires login; the shipping step collects a delivery address.
Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function (): void {
    Route::get('/', [CheckoutController::class, 'shipping'])->name('shipping');
    Route::get('/methods', [CheckoutController::class, 'methods'])->name('methods');
    Route::post('/shipping', [CheckoutController::class, 'storeShipping'])->name('shipping.store');
    Route::get('/payment', [CheckoutController::class, 'payment'])->name('payment');
    Route::post('/payment', [PaymentController::class, 'initiate'])->name('payment.initiate');
    Route::get('/callback', [PaymentController::class, 'callback'])->name('callback');
    Route::get('/confirmation/{order}', [PaymentController::class, 'confirmation'])->name('confirmation');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/brands/{slug}', [BrandController::class, 'show'])->name('brands.show');

// Tag = SEO landing page for a category+attribute filter (see docs/TAGS.md).
Route::get('/tags/{slug}', [TagController::class, 'show'])->name('tags.show');

Route::get('/faq/{position?}', [FaqController::class, 'show'])->name('faqs.show');

Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Wishlist has no guest/session support (unlike cart) — user_id is required
// at the schema level, so toggling is auth-only.
Route::post('/products/{product}/wishlist', [WishlistController::class, 'toggle'])
    ->middleware('auth')
    ->name('wishlist.toggle');

// Any logged-in user can submit a review; it's created PENDING and only shows
// once an admin approves it. Verified-buyer badge is computed at read time.
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])
    ->middleware('auth')
    ->name('reviews.store');

// CMS pages use clean top-level slugs (e.g. /about-us). Keep this LAST so it
// only matches single-segment paths no other route claimed; unknown slugs 404.
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show');
