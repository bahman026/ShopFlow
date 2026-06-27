<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Cache;

class VerifyOtpCode
{
    /**
     * Wrong attempts allowed before the code is invalidated.
     */
    public const MAX_ATTEMPTS = 5;

    /**
     * Check a submitted code against the stored one. The code is consumed on
     * success and dropped after too many wrong attempts.
     */
    public function __invoke(string $mobile, string $code): bool
    {
        $key = SendOtpCode::key($mobile);

        /** @var array{code: string, attempts: int, expires_at: int}|null $stored */
        $stored = Cache::get($key);

        if ($stored === null) {
            return false;
        }

        if ($stored['expires_at'] <= now()->getTimestamp()) {
            Cache::forget($key);

            return false;
        }

        if (hash_equals($stored['code'], $code)) {
            Cache::forget($key);

            return true;
        }

        $attempts = $stored['attempts'] + 1;
        $remaining = $stored['expires_at'] - now()->getTimestamp();

        if ($attempts >= self::MAX_ATTEMPTS || $remaining <= 0) {
            Cache::forget($key);
        } else {
            // Keep the original expiry; wrong tries must not extend the window.
            Cache::put($key, [
                'code' => $stored['code'],
                'attempts' => $attempts,
                'expires_at' => $stored['expires_at'],
            ], now()->addSeconds($remaining));
        }

        return false;
    }
}
