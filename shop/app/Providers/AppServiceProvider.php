<?php

namespace App\Providers;

use App\Contracts\ProductSearch;
use App\Contracts\SmsSender;
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
        //
    }
}
