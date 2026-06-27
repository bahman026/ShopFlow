<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('redirects guests to login', function (): void {
    $this->get('/account')->assertRedirect('/login');
});

it('shows the dashboard for an authenticated user', function (): void {
    $user = User::factory()->create(['first_name' => 'علی', 'last_name' => 'رضایی']);

    $this->actingAs($user)
        ->get('/account')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Dashboard')
            ->where('user.displayName', 'علی رضایی')
        );
});

it('shows the profile form with the user data', function (): void {
    $user = User::factory()->create([
        'first_name' => 'علی',
        'last_name' => 'رضایی',
        'email' => 'ali@example.com',
    ]);

    $this->actingAs($user)
        ->get('/account/profile')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Profile')
            ->where('user.firstName', 'علی')
            ->where('user.email', 'ali@example.com')
        );
});

it('hides the placeholder email in the profile form', function (): void {
    $user = User::factory()->create([
        'mobile' => '09120000001',
        'email' => User::placeholderEmail('09120000001'),
    ]);

    $this->actingAs($user)
        ->get('/account/profile')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('user.email', null)
        );
});

it('updates the profile', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/account/profile', [
            'first_name' => 'مریم',
            'last_name' => 'کریمی',
            'email' => 'maryam@example.com',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => 'مریم',
        'last_name' => 'کریمی',
        'email' => 'maryam@example.com',
    ]);
});

it('requires a first and last name', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/account/profile', ['first_name' => '', 'last_name' => ''])
        ->assertSessionHasErrors(['first_name', 'last_name']);
});

it('rejects an email already used by another account', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/account/profile', [
            'first_name' => 'a',
            'last_name' => 'b',
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors('email');
});

it('renders a placeholder for sections not built yet', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/account/orders')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/ComingSoon')
            ->where('title', 'سفارش‌های من')
        );
});
