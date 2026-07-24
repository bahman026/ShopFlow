<?php

declare(strict_types=1);

use App\Actions\Auth\SendOtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia;

function otpFor(string $mobile): string
{
    /** @var array{code: string} $stored */
    $stored = Cache::get(SendOtpCode::key($mobile));

    return $stored['code'];
}

it('renders the login page', function (): void {
    $this->get('/login')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->component('Auth/Login'));
});

it('sends an otp for a new mobile number', function (): void {
    $this->post('/login/identify', ['mobile' => '09123456789'])
        ->assertRedirect()
        ->assertSessionHas('authStep', 'otp');

    expect(Cache::has(SendOtpCode::key('09123456789')))->toBeTrue();
});

it('registers and logs in a new user via otp', function (): void {
    $this->post('/login/identify', ['mobile' => '09123456789']);
    $code = otpFor('09123456789');

    $this->post('/login/otp/verify', ['mobile' => '09123456789', 'code' => $code])
        ->assertRedirect('/');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['mobile' => '09123456789']);
});

it('normalizes persian-digit mobile numbers', function (): void {
    $this->post('/login/identify', ['mobile' => '۰۹۱۲۳۴۵۶۷۸۹'])
        ->assertSessionHas('authMobile', '09123456789');
});

it('rejects an invalid mobile number', function (): void {
    $this->post('/login/identify', ['mobile' => '12345'])
        ->assertSessionHasErrors('mobile');

    $this->assertGuest();
});

it('blocks resending a code while the current one is still valid', function (): void {
    $this->post('/login/identify', ['mobile' => '09123456789']);
    $code = otpFor('09123456789');

    $this->post('/login/otp', ['mobile' => '09123456789'])
        ->assertSessionHasErrors('code')
        ->assertSessionHas('authStep', 'otp');

    expect(otpFor('09123456789'))->toBe($code);
});

it('issues a new code once the previous one has expired', function (): void {
    $this->post('/login/identify', ['mobile' => '09123456789']);
    $first = otpFor('09123456789');

    $this->travel(SendOtpCode::TTL + 1)->seconds();

    $this->post('/login/otp', ['mobile' => '09123456789'])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('authStep', 'otp');

    expect(otpFor('09123456789'))->not->toBe($first);
});

it('rejects a wrong otp code', function (): void {
    $this->post('/login/identify', ['mobile' => '09123456789']);

    $this->post('/login/otp/verify', ['mobile' => '09123456789', 'code' => '00000'])
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});

it('logs in an existing user via otp and verifies the mobile', function (): void {
    User::factory()->create([
        'mobile' => '09120000005',
        'mobile_verified_at' => null,
    ]);

    $this->post('/login/identify', ['mobile' => '09120000005']);
    $code = otpFor('09120000005');

    $this->post('/login/otp/verify', ['mobile' => '09120000005', 'code' => $code])
        ->assertRedirect('/');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['mobile' => '09120000005', 'mobile_verified_at' => now()->toDateTimeString()]);
});

it('logs in with a correct password', function (): void {
    User::factory()->create(['mobile' => '09120000001', 'password' => bcrypt('secret123')]);

    $this->post('/login/password', ['mobile' => '09120000001', 'password' => 'secret123'])
        ->assertRedirect('/');

    $this->assertAuthenticated();
});

it('rejects a wrong password', function (): void {
    User::factory()->create(['mobile' => '09120000001', 'password' => bcrypt('secret123')]);

    $this->post('/login/password', ['mobile' => '09120000001', 'password' => 'wrong'])
        ->assertSessionHasErrors('password');

    $this->assertGuest();
});

it('blocks a blocked user from logging in', function (): void {
    User::factory()->blocked()->create(['mobile' => '09120000009', 'password' => bcrypt('secret123')]);

    $this->post('/login/password', ['mobile' => '09120000009', 'password' => 'secret123'])
        ->assertSessionHasErrors('mobile');

    $this->assertGuest();
});

it('logs out an authenticated user', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect('/');

    $this->assertGuest();
});
