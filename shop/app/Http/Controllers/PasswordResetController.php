<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Auth\LoginUser;
use App\Actions\Auth\NormalizeMobile;
use App\Actions\Auth\SendOtpCode;
use App\Actions\Auth\VerifyOtpCode;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Password reset over two channels: the customer's mobile (one-time code, the
 * same OTP infrastructure the login uses) or their email (a signed reset link
 * through Laravel's password broker).
 *
 * Accounts created by OTP sign-up have a random password they have never seen,
 * so this flow doubles as "set a password for the first time".
 */
class PasswordResetController extends Controller
{
    /**
     * Session key holding the mobile whose OTP was just verified.
     */
    private const SESSION_KEY = 'password_reset';

    /**
     * How long a verified OTP stays good for before the new password has to be
     * submitted (seconds).
     */
    private const VERIFIED_TTL = 900;

    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Mobile channel, step 1: send a one-time code to a known account.
     */
    public function sendOtp(Request $request, NormalizeMobile $normalize, SendOtpCode $sendOtp): RedirectResponse
    {
        $mobile = $this->mobile($request, $normalize);
        $this->userByMobile($mobile);

        return $this->sentOtp($mobile, $sendOtp);
    }

    /**
     * Mobile channel: resend the code. Blocked while the current one is still
     * valid, so repeated taps cannot reset the expiry.
     */
    public function resendOtp(Request $request, NormalizeMobile $normalize, SendOtpCode $sendOtp): RedirectResponse
    {
        $mobile = $this->mobile($request, $normalize);
        $this->userByMobile($mobile);

        $remaining = $sendOtp->secondsRemaining($mobile);

        if ($remaining > 0) {
            return back()
                ->withErrors(['code' => trans('messages.auth.code_still_valid', ['seconds' => $remaining])])
                ->with('resetStep', 'otp')
                ->with('resetMobile', $mobile)
                ->with('resetResendIn', $remaining);
        }

        return $this->sentOtp($mobile, $sendOtp);
    }

    /**
     * Mobile channel, step 2: verify the code. Success unlocks the
     * choose-a-password step for a limited window.
     */
    public function verifyOtp(Request $request, NormalizeMobile $normalize, VerifyOtpCode $verify): RedirectResponse
    {
        $mobile = $this->mobile($request, $normalize);
        $this->userByMobile($mobile);

        if (! $verify($mobile, $this->code($request))) {
            return back()
                ->withErrors(['code' => trans('messages.auth.code_invalid')])
                ->with('resetStep', 'otp')
                ->with('resetMobile', $mobile);
        }

        $request->session()->put(self::SESSION_KEY, [
            'mobile' => $mobile,
            'verified_at' => now()->getTimestamp(),
        ]);

        return back()
            ->with('resetStep', 'password')
            ->with('resetMobile', $mobile);
    }

    /**
     * Mobile channel, step 3: store the new password and log the customer in —
     * they have just proven they hold the number, which is exactly what OTP
     * login proves.
     */
    public function updateWithMobile(Request $request, LoginUser $login): RedirectResponse
    {
        $mobile = $this->verifiedMobile($request);

        if ($mobile === null) {
            return redirect()->route('password.request')
                ->withErrors(['mobile' => trans('messages.auth.reset.session_expired')]);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = $this->userByMobile($mobile);
        $user->password = $validated['password'];
        $user->mobile_verified_at ??= now();
        $user->setRememberToken(Str::random(60));
        $user->save();

        $request->session()->forget(self::SESSION_KEY);

        $login($request, $user);

        return redirect()->route('account.dashboard')
            ->with('status', trans('messages.auth.reset.done'));
    }

    /**
     * Email channel: mail a reset link. The response never reveals whether the
     * address belongs to an account.
     */
    public function sendEmailLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = $validated['email'];
        $user = User::query()->where('email', $email)->first();

        // OTP sign-ups carry a synthetic placeholder address that cannot
        // receive mail, and blocked accounts must not be recoverable.
        $mailable = $user !== null
            && ! $user->hasPlaceholderEmail()
            && $user->status !== UserStatusEnum::BLOCK;

        if ($mailable) {
            $status = Password::sendResetLink(['email' => $email]);

            if ($status === Password::RESET_THROTTLED) {
                return back()
                    ->withErrors(['email' => trans('messages.auth.reset.email_throttled')])
                    ->with('resetChannel', 'email');
            }
        }

        return back()
            ->with('resetChannel', 'email')
            ->with('resetEmailSent', true)
            ->with('status', trans('messages.auth.reset.email_sent'));
    }

    /**
     * Email channel: the page the mailed link opens.
     */
    public function edit(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    /**
     * Email channel: consume the token, store the new password, log in.
     */
    public function update(Request $request, LoginUser $login): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $reset = null;

        $status = Password::reset($validated, function (User $user, string $password) use (&$reset): void {
            $user->password = $password;
            $user->setRememberToken(Str::random(60));
            $user->save();

            $reset = $user;
        });

        if ($status !== Password::PASSWORD_RESET || ! $reset instanceof User) {
            return back()->withErrors(['email' => trans('messages.auth.reset.token_invalid')]);
        }

        if ($reset->status === UserStatusEnum::BLOCK) {
            return redirect()->route('login')->withErrors(['mobile' => trans('messages.auth.blocked')]);
        }

        $login($request, $reset);

        return redirect()->route('account.dashboard')
            ->with('status', trans('messages.auth.reset.done'));
    }

    /**
     * The mobile whose code was verified, or null when that never happened or
     * the window has closed.
     */
    private function verifiedMobile(Request $request): ?string
    {
        /** @var array{mobile: string, verified_at: int}|null $verified */
        $verified = $request->session()->get(self::SESSION_KEY);

        if ($verified === null) {
            return null;
        }

        if (now()->getTimestamp() - $verified['verified_at'] > self::VERIFIED_TTL) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return $verified['mobile'];
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
                    ->withErrors(['mobile' => trans('messages.auth.mobile_invalid')])
                    ->with('resetStep', 'mobile')
            );
        }

        return $mobile;
    }

    /**
     * The account behind a mobile number. Unlike login, password reset never
     * creates an account — there is nothing to reset for an unknown number.
     */
    private function userByMobile(string $mobile): User
    {
        $user = User::query()->where('mobile', $mobile)->first();

        if ($user === null) {
            throw new HttpResponseException(
                back()
                    ->withErrors(['mobile' => trans('messages.auth.reset.no_account')])
                    ->with('resetStep', 'mobile')
                    ->with('resetMobile', $mobile)
            );
        }

        if ($user->status === UserStatusEnum::BLOCK) {
            throw new HttpResponseException(
                back()
                    ->withErrors(['mobile' => trans('messages.auth.blocked')])
                    ->with('resetStep', 'mobile')
            );
        }

        return $user;
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

        // The provider refused the message and nothing was stored, so keep the
        // customer on the mobile step where they can try again immediately
        // rather than sending them to a code form no code will ever arrive for.
        if ($code === null) {
            return back()
                ->withErrors(['mobile' => trans('messages.auth.code_send_failed')])
                ->with('resetStep', 'mobile')
                ->with('resetMobile', $mobile);
        }

        $redirect = back()
            ->with('resetStep', 'otp')
            ->with('resetMobile', $mobile)
            ->with('resetResendIn', $sendOtp->secondsRemaining($mobile));

        if (config('app.debug')) {
            $redirect->with('authOtpDev', $code);
        }

        return $redirect;
    }
}
