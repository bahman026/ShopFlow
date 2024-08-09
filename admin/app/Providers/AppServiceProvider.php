<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Category;
use App\Policies\CategoryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../infrastructure/migrations');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            return 'App\\Policies\\' . class_basename($modelClass) . 'Policy';
        });
        Gate::policy(Category::class, CategoryPolicy::class);
    }
}
