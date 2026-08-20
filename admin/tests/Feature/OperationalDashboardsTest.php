<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

// Pulse and the log viewer are not Filament resources, so the permission
// system does not cover them — and both ship open by default. They show slow
// queries, exception messages and whole stack traces, so the gates in
// AppServiceProvider are the only thing in front of them.

it('lets a super-admin open the pulse dashboard', function (): void {
    login();

    get('/pulse')->assertOk();
});

it('lets a super-admin open the log viewer', function (): void {
    login();

    get('/log-viewer')->assertOk();
});

it('keeps pulse away from a plain admin', function (): void {
    loginAsAdmin();

    // Day-to-day staff run the catalogue and orders; server internals and
    // exception traces are not part of that job.
    get('/pulse')->assertForbidden();
});

it('keeps the log viewer away from a plain admin', function (): void {
    loginAsAdmin();

    get('/log-viewer')->assertForbidden();
});

it('keeps both dashboards away from a storefront customer', function (): void {
    Role::findOrCreate(RolesEnum::USER->value);
    /** @var User $customer */
    $customer = User::factory()->create();
    $customer->assignRole(RolesEnum::USER->value);
    actingAs($customer);

    // The two apps share the `users` table, so a shop customer is a real user
    // here — they must reach neither dashboard.
    get('/pulse')->assertForbidden();
    get('/log-viewer')->assertForbidden();
});

it('keeps both dashboards away from a guest', function (): void {
    get('/pulse')->assertForbidden();
    get('/log-viewer')->assertForbidden();
});

// The storefront runs in its own container. The viewer is configured with one
// folder per app so the two are never mixed, and the storefront path is an env
// value because in production it is a shared volume, not a sibling directory.

it('shows the admin and storefront logs as two separate folders', function (): void {
    $groups = collect(config('log-viewer.include_files'))->values();

    expect($groups)->toContain('Admin panel')
        ->and($groups)->toContain('Storefront');
});

it('points the storefront folder at a directory that exists', function (): void {
    $paths = collect(config('log-viewer.include_files'))->keys();

    $shopGlob = $paths->first(fn (string $path): bool => str_contains($path, 'shop'));

    expect($shopGlob)->not->toBeNull()
        ->and(File::isDirectory(dirname((string) $shopGlob)))->toBeTrue(
            'the storefront log directory is not reachable from the admin app'
        );
});

it('writes the shared channel where the viewer reads', function (): void {
    // LOG_STACK=stderr,shared in production: stderr keeps `docker logs` intact,
    // `shared` adds the file the viewer needs. If the channel is missing the
    // viewer silently shows nothing.
    expect(config('logging.channels.shared'))->not->toBeNull()
        ->and(config('logging.channels.shared.driver'))->toBe('daily');
});
