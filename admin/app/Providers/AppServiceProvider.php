<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\RolesEnum;
use App\Models\Order;
use App\Models\User;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Gate;
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

        $this->gateOperationalDashboards();
    }

    /**
     * Restrict the Pulse and log-viewer dashboards to super-admins.
     *
     * Both ship wide open by default, and both expose far more than the panel
     * does: Pulse shows slow queries and exception messages, the log viewer
     * shows whole stack traces. Neither is behind a Filament resource, so the
     * permission system does not reach them — these gates are the only thing
     * standing in front of them.
     *
     * `admin` is deliberately not enough. Day-to-day staff run the catalogue
     * and orders; server internals are not part of that job.
     */
    private function gateOperationalDashboards(): void
    {
        $superAdminOnly = static fn (User $user): bool => $user->hasRole(RolesEnum::SUPER_ADMIN->value);

        Gate::define('viewPulse', $superAdminOnly);
        Gate::define('viewLogViewer', $superAdminOnly);
    }
}
