<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\RolesEnum;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Variety;
use App\Observers\AttributeGroupObserver;
use App\Observers\AttributeObserver;
use App\Observers\BrandObserver;
use App\Observers\CategoryObserver;
use App\Observers\ImageObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\ReviewObserver;
use App\Observers\VarietyObserver;
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

        $this->invalidateCatalogCache();

        $this->gateOperationalDashboards();
    }

    /**
     * Clear the storefront's cached product pages and listings whenever staff
     * change the catalog.
     *
     * The entries are written by the *other* app. That only works because both
     * apps point at the same Redis store with the same pinned prefixes (see
     * `config/cache.php`) and build keys with the same mirrored
     * `App\Support\ProductCache`. Get either wrong and every call here still
     * succeeds while reaching nothing — the storefront would serve edited-away
     * prices until each entry's TTL expired, with no error anywhere.
     *
     * The same observers are registered on the storefront side, because it
     * writes these tables too (inventory on payment, the product view counter).
     *
     * Two groups, and the difference matters. The first four know which product
     * they belong to, so they *forget* that product's page precisely — they fire
     * often (every sale moves inventory) and have to stay cheap. The metadata
     * observers below cannot: a renamed attribute group reaches most of the
     * catalog through three pivots at once, so they bump the generation and let
     * everything rebuild. That is affordable only because they fire rarely.
     */
    private function invalidateCatalogCache(): void
    {
        Product::observe(ProductObserver::class);
        Variety::observe(VarietyObserver::class);
        Image::observe(ImageObserver::class);
        Review::observe(ReviewObserver::class);

        Category::observe(CategoryObserver::class);
        Brand::observe(BrandObserver::class);
        Attribute::observe(AttributeObserver::class);
        AttributeGroup::observe(AttributeGroupObserver::class);
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
