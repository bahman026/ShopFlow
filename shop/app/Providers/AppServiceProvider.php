<?php

namespace App\Providers;

use App\Contracts\ProductSearch;
use App\Contracts\SmsSender;
use App\Models\Image;
use App\Models\Product;
use App\Models\Review;
use App\Models\Variety;
use App\Observers\ImageObserver;
use App\Observers\ProductObserver;
use App\Observers\ReviewObserver;
use App\Observers\VarietyObserver;
use App\Search\DatabaseProductSearch;
use App\Sms\LogSmsSender;
use App\Sms\SmsIrSender;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Keyword search runs on the database for now. Swap this binding to an
        // Elasticsearch-backed implementation later without changing callers.
        $this->app->bind(ProductSearch::class, DatabaseProductSearch::class);

        // OTP delivery. A configured sms.ir key is what turns real sending on,
        // so production opts in by filling the key while local and CI keep
        // logging the code — nothing is ever sent by accident, and a fresh
        // clone works with no credentials.
        $this->app->bind(SmsSender::class, function (): SmsSender {
            $apiKey = config('services.sms_ir.api_key');

            return is_string($apiKey) && $apiKey !== ''
                ? new SmsIrSender
                : new LogSmsSender;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->invalidateCatalogCache();
    }

    /**
     * Clear this app's own cached product pages and listings when it writes to
     * the catalog.
     *
     * The storefront is mostly a reader, but not entirely: a paid order
     * decrements `varieties.inventory` (`DecrementInventoryAndMarkPaid`), which
     * is the stock count a cached product page shows. Leaving invalidation to
     * the admin panel would mean a sold-out variety still advertising stock
     * until the entry expired.
     *
     * The same four observers are registered in the admin panel, and both apps
     * build keys with the same mirrored `App\Support\ProductCache` against one
     * shared Redis store, so either side's write clears the other side's cache.
     *
     * `Image` and `Review` do not fire from any storefront path that exists
     * today (reviews are created `PENDING`, so nothing visible changes) — they
     * are registered because this app shares the schema with the panel and a
     * future write here must not be the one place that forgets to invalidate.
     */
    private function invalidateCatalogCache(): void
    {
        Product::observe(ProductObserver::class);
        Variety::observe(VarietyObserver::class);
        Image::observe(ImageObserver::class);
        Review::observe(ReviewObserver::class);
    }
}
