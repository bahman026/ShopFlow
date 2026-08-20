<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keeps varieties.inventory in step with order status changes made in
        // the panel — the storefront covers gateway payments on its own side.
        Order::observe(OrderObserver::class);
    }
}
