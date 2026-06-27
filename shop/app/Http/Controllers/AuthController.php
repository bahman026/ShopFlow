<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Auth\NormalizeMobile;
use App\Actions\Auth\SendOtpCode;
use App\Actions\Auth\VerifyOtpCode;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Step 1: take a mobile number and send a one-time code. OTP is the primary
     * path for everyone; password login is offered as an alternative on the
     * next screen.
     */
    public function identify(Request $request, NormalizeMobile $normalize, SendOtpCode $sendOtp): RedirectResponse
    {
        return $this->sentOtp($this->mobile($request, $normalize), $sendOtp);
    }

    /**
     * Resend a one-time code — also used to switch from password back to OTP
     * login. Resending is blocked while the current code is still valid.
     */
    public function requestOtp(Request $request, NormalizeMobile $normalize, SendOtpCode $sendOtp): RedirectResponse
    {
        $mobile = $this->mobile($request, $normalize);
        $remaining = $sendOtp->secondsRemaining($mobile);

        if ($remaining > 0) {
            return back()
                ->withErrors(['code' => "کد قبلی هنوز معتبر است؛ لطفاً {$remaining} ثانیه دیگر برای دریافت کد جدید صبر کنید."])
                ->with('authStep', 'otp')
                ->with('authMobile', $mobile)
                ->with('authResendIn', $remaining);
        }

        return $this->sentOtp($mobile, $sendOtp);
    }

    /**
     * Verify a one-time code, then log in the user (creating the account on
     * first login).
     */
    public function verifyOtp(Request $request, NormalizeMobile $normalize, VerifyOtpCode $verify): RedirectResponse
    {
        $mobile = $this->mobile($request, $normalize);
        $code = $this->code($request);

        if (! $verify($mobile, $code)) {
            return back()
                ->withErrors(['code' => 'کد وارد شده نادرست یا منقضی شده است.'])
                ->with('authStep', 'otp')
                ->with('authMobile', $mobile);
        }

        $user = User::query()->firstOrNew(['mobile' => $mobile]);

        if ($user->exists && $user->status === UserStatusEnum::BLOCK) {
            return $this->blocked($mobile);
        }

        if (! $user->exists) {
            // The shared schema requires email/password/name to be present, so
            // seed safe placeholders the user can complete later in their
            // profile. The random password keeps the account OTP-only.
            $user->status = UserStatusEnum::ACTIVE;
            $user->first_name = '';
            $user->last_name = '';
            $user->email = $mobile.'@mobile.shopflow.local';
            $user->password = Hash::make(Str::random(40));
        }

        $user->mobile_verified_at ??= now();
        $user->save();

        return $this->login($request, $user);
    }

    /**
     * Log in with mobile + password.
     */
    public function password(Request $request, NormalizeMobile $normalize): RedirectResponse
    {
        $mobile = $this->mobile($request, $normalize);
        $password = (string) $request->input('password', '');

        $user = User::query()->where('mobile', $mobile)->first();

        if ($user === null || ! Hash::check($password, $user->password ?? '')) {
            return back()
                ->withErrors(['password' => 'رمز عبور نادرست است.'])
                ->with('authStep', 'password')
                ->with('authMobile', $mobile);
        }

        if ($user->status === UserStatusEnum::BLOCK) {
            return $this->blocked($mobile);
        }

        return $this->login($request, $user);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Validate and normalise the submitted mobile, aborting back with an error
     * when it is not a valid Iranian number.
     */
    private function mobile(Request $request, NormalizeMobile $normalize): string
    {
        $mobile = $normalize((string) $request->input('mobile', ''));

        if ($mobile === null) {
            throw new HttpResponseException(
                back()
                    ->withErrors(['mobile' => 'شماره موبایل معتبر نیست.'])
                    ->with('authStep', 'mobile')
            );
        }

        return $mobile;
    }

    private function code(Request $request): string
    {
        $code = strtr((string) $request->input('code', ''), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);

        return preg_replace('/\D+/', '', $code) ?? '';
    }

    private function sentOtp(string $mobile, SendOtpCode $sendOtp): RedirectResponse
    {
        $code = $sendOtp($mobile);

        $redirect = back()
            ->with('authStep', 'otp')
            ->with('authMobile', $mobile)
            ->with('authResendIn', $sendOtp->secondsRemaining($mobile));

        if (config('app.debug')) {
            $redirect->with('authOtpDev', $code);
        }

        return $redirect;
    }

    private function blocked(string $mobile): RedirectResponse
    {
        return back()
            ->withErrors(['mobile' => 'حساب کاربری شما مسدود شده است.'])
            ->with('authStep', 'mobile')
            ->with('authMobile', $mobile);
    }

    private function login(Request $request, User $user): RedirectResponse
    {
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }
}
