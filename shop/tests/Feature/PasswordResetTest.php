<?php

declare(strict_types=1);

use App\Actions\Auth\SendOtpCode;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Notifications\ResetPasswordLink;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia;

function resetUser(string $mobile = '09121112233', string $email = 'buyer@example.com'): User
{
    return User::create([
        'first_name' => 'مشتری',
        'last_name' => 'نمونه',
        'email' => $email,
        'mobile' => $mobile,
        'password' => Hash::make('old-password'),
        'status' => UserStatusEnum::ACTIVE,
    ]);
}

function resetOtpFor(string $mobile): string
{
    /** @var array{code: string} $stored */
    $stored = Cache::get(SendOtpCode::key($mobile));

    return $stored['code'];
}

it('renders the forgot-password page', function (): void {
    $this->get('/forgot-password')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->component('Auth/ForgotPassword'));
});

it('sends a reset code to a known mobile number', function (): void {
    resetUser();

    $this->post('/forgot-password/otp', ['mobile' => '09121112233'])
        ->assertRedirect()
        ->assertSessionHas('resetStep', 'otp');

    expect(Cache::has(SendOtpCode::key('09121112233')))->toBeTrue();
});

it('shares the reset step with the page so the form can advance', function (): void {
    resetUser();

    $this->post('/forgot-password/otp', ['mobile' => '09121112233']);

    $this->get('/forgot-password')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('flash.resetStep', 'otp')
            ->where('flash.resetMobile', '09121112233')
            ->etc());
});

it('does not send a reset code to an unknown mobile number', function (): void {
    $this->post('/forgot-password/otp', ['mobile' => '09129998877'])
        ->assertSessionHasErrors('mobile');

    expect(Cache::has(SendOtpCode::key('09129998877')))->toBeFalse();
});

it('refuses to reset the password of a blocked account', function (): void {
    $user = resetUser();
    $user->status = UserStatusEnum::BLOCK;
    $user->save();

    $this->post('/forgot-password/otp', ['mobile' => '09121112233'])
        ->assertSessionHasErrors('mobile');
});

it('sets a new password after verifying the mobile code and logs the user in', function (): void {
    $user = resetUser();

    $this->post('/forgot-password/otp', ['mobile' => '09121112233']);
    $code = resetOtpFor('09121112233');

    $this->post('/forgot-password/otp/verify', ['mobile' => '09121112233', 'code' => $code])
        ->assertSessionHas('resetStep', 'password');

    $this->post('/forgot-password/mobile', [
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect('/account');

    $this->assertAuthenticatedAs($user->fresh());
    expect(Hash::check('new-password-123', (string) $user->fresh()?->password))->toBeTrue();
});

it('rejects a wrong mobile code', function (): void {
    resetUser();

    $this->post('/forgot-password/otp', ['mobile' => '09121112233']);

    $this->post('/forgot-password/otp/verify', ['mobile' => '09121112233', 'code' => '00000'])
        ->assertSessionHasErrors('code');

    $this->post('/forgot-password/mobile', [
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect('/forgot-password');

    $this->assertGuest();
});

it('rejects a new password that is not confirmed', function (): void {
    resetUser();

    $this->post('/forgot-password/otp', ['mobile' => '09121112233']);
    $code = resetOtpFor('09121112233');
    $this->post('/forgot-password/otp/verify', ['mobile' => '09121112233', 'code' => $code]);

    $this->post('/forgot-password/mobile', [
        'password' => 'new-password-123',
        'password_confirmation' => 'something-else',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

it('mails a reset link for a known email address', function (): void {
    Notification::fake();

    $user = resetUser();

    $this->post('/forgot-password/email', ['email' => 'buyer@example.com'])
        ->assertSessionHas('resetEmailSent', true);

    Notification::assertSentTo($user, ResetPasswordLink::class);
});

it('reports success without mailing anything for an unknown email address', function (): void {
    Notification::fake();

    $this->post('/forgot-password/email', ['email' => 'nobody@example.com'])
        ->assertSessionHas('resetEmailSent', true);

    Notification::assertNothingSent();
});

it('never mails the synthetic placeholder address of an otp signup', function (): void {
    Notification::fake();

    $mobile = '09121112233';
    resetUser($mobile, User::placeholderEmail($mobile));

    $this->post('/forgot-password/email', ['email' => User::placeholderEmail($mobile)])
        ->assertSessionHas('resetEmailSent', true);

    Notification::assertNothingSent();
});

it('renders the reset page the mailed link opens', function (): void {
    $this->get('/reset-password/some-token?email=buyer@example.com')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Auth/ResetPassword')
            ->where('token', 'some-token')
            ->where('email', 'buyer@example.com'));
});

it('sets a new password from a valid email token and logs the user in', function (): void {
    $user = resetUser();
    $token = Password::broker()->createToken($user);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => 'buyer@example.com',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect('/account');

    $this->assertAuthenticatedAs($user->fresh());
    expect(Hash::check('new-password-123', (string) $user->fresh()?->password))->toBeTrue();
});

it('rejects an invalid email token', function (): void {
    resetUser();

    $this->post('/reset-password', [
        'token' => 'not-a-real-token',
        'email' => 'buyer@example.com',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
