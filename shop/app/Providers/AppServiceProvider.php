<?php

namespace App\Providers;

use App\Contracts\ProductSearch;
use App\Search\DatabaseProductSearch;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
