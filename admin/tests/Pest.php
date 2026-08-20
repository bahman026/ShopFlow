<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * A logged-in super-admin.
 *
 * Roles and permissions come from RolePermissionSeeder rather than being
 * hand-rolled, so resource authorization behaves in tests exactly as it does in
 * the panel — a resource that a real super-admin could not reach must not be
 * reachable here either.
 */
function login(?User $user = null): void
{
    $user ??= User::factory()->create();

    app(RolePermissionSeeder::class)->run();

    $user->assignRole(RolesEnum::SUPER_ADMIN->value);
    actingAs($user);
}

/**
 * A logged-in admin — the day-to-day staff role, which deliberately cannot
 * reach settings, gateways or staff accounts.
 */
function loginAsAdmin(?User $user = null): User
{
    $user ??= User::factory()->create();

    app(RolePermissionSeeder::class)->run();

    $user->assignRole(RolesEnum::ADMIN->value);
    actingAs($user);

    return $user;
}
