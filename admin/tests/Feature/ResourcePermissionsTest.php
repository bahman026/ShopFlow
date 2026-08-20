<?php

declare(strict_types=1);

use App\Enums\PermissionActionEnum;
use App\Enums\PermissionGroupEnum;
use App\Enums\RolesEnum;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\GatewayResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\SettingResource;
use App\Filament\Resources\UserResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\actingAs;

// Every panel resource is gated by a PermissionGroupEnum. Before this, none of
// them declared any authorization at all, so the permissions table was
// decorative and any admin had full access to everything.

/**
 * @return array<int, class-string>
 */
function allResourceClasses(): array
{
    return collect(glob(app_path('Filament/Resources/*Resource.php')) ?: [])
        ->map(fn (string $path): string => 'App\\Filament\\Resources\\' . basename($path, '.php'))
        ->all();
}

it('gates every resource behind a permission group', function () {
    $ungated = collect(allResourceClasses())
        ->reject(fn (string $class): bool => method_exists($class, 'permissionGroup'))
        ->all();

    expect($ungated)->toBe([], 'Resources with no permission group: ' . implode(', ', $ungated));
});

it('lets a super-admin reach every resource', function () {
    login();

    foreach (allResourceClasses() as $resource) {
        expect($resource::canViewAny())->toBeTrue("super-admin cannot view {$resource}");
    }
});

it('keeps settings and payment gateways away from a plain admin', function () {
    loginAsAdmin();

    // Day-to-day staff run the catalogue and orders...
    expect(ProductResource::canViewAny())->toBeTrue()
        ->and(OrderResource::canViewAny())->toBeTrue();

    // ...but settings and gateway credentials are super-admin territory.
    expect(SettingResource::canViewAny())->toBeFalse()
        ->and(GatewayResource::canViewAny())->toBeFalse()
        ->and(SettingResource::canCreate())->toBeFalse()
        ->and(GatewayResource::canCreate())->toBeFalse();
});

it('lets an admin process orders without letting them delete the history', function () {
    loginAsAdmin();

    $order = Order::factory()->create();

    expect(OrderResource::canEdit($order))->toBeTrue()
        ->and(OrderResource::canDelete($order))->toBeFalse()
        ->and(OrderResource::canDeleteAny())->toBeFalse();
});

it('denies everything to a panel user holding no permissions', function () {
    app(RolePermissionSeeder::class)->run();

    $user = User::factory()->create();
    $user->assignRole(RolesEnum::USER->value);
    actingAs($user);

    foreach (allResourceClasses() as $resource) {
        expect($resource::canViewAny())->toBeFalse("{$resource} is reachable with no permissions");
    }
});

it('denies everything when nobody is logged in', function () {
    Auth::logout();

    expect(ProductResource::canViewAny())->toBeFalse()
        ->and(OrderResource::canViewAny())->toBeFalse();
});

it('still lets a policy tighten what the permission allows', function () {
    login();

    $leaf = Category::factory()->create();
    $withProduct = Category::factory()->create();
    Product::factory()->create(['category_id' => $withProduct->id]);

    // The super-admin holds delete_catalog for both, but CategoryPolicy
    // refuses the one that still has products pointing at it.
    expect(CategoryResource::canDelete($leaf))->toBeTrue()
        ->and(CategoryResource::canDelete($withProduct))->toBeFalse();
});

it('keeps creating panel users to super-admins even with the permission', function () {
    loginAsAdmin();

    // The admin role holds create_customers, but staff accounts stay
    // super-admin only.
    expect(UserResource::canViewAny())->toBeTrue()
        ->and(UserResource::canCreate())->toBeFalse();

    login();
    expect(UserResource::canCreate())->toBeTrue();
});

it('names every permission the seeder grants', function () {
    app(RolePermissionSeeder::class)->run();

    $expected = PermissionActionEnum::all();

    expect($expected)->toHaveCount(count(PermissionGroupEnum::cases()) * count(PermissionActionEnum::cases()))
        ->and($expected)->toContain('view_orders', 'delete_settings', 'update_catalog');
});
