<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('can render admin login page.', function () {
    get(route('filament.admin.auth.login'))
        ->assertOk();
});

test('unauthenticated users can not access admin dashboard.', function () {
    get(route('filament.admin.pages.dashboard'))
        ->assertRedirectToRoute('filament.admin.auth.login');
});

test('authenticated users can access admin dashboard.', function () {
    login();

    get(route('filament.admin.pages.dashboard'))->assertOk();
});
