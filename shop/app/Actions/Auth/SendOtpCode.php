<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendOtpCode
{
    /**
     * One-time code length.
     */
    public const LENGTH = 5;

    /**
     * Seconds a code stays valid. A new code cannot be requested until the
     * current one expires.
     */
    public const TTL = 120;

    /**
     * Generate a one-time code, store it for verification, and "send" it.
     *
     * It is idempotent within the validity window: while an unexpired code
     * exists it is reused (no new code, no SMS, no extended lifetime), so
     * repeated requests cannot reset the expiry. SMS delivery is stubbed
     * (logged) for now; swap in a provider later. The active code is returned so
     * it can be surfaced in non-production environments for testing.
     */
    public function __invoke(string $mobile): string
    {
        $existing = $this->current($mobile);

        if ($existing !== null && $existing['expires_at'] > now()->getTimestamp()) {
            return $existing['code'];
        }

        $code = str_pad((string) random_int(0, 10 ** self::LENGTH - 1), self::LENGTH, '0', STR_PAD_LEFT);

        Cache::put($this->key($mobile), [
            'code' => $code,
            'attempts' => 0,
            'expires_at' => now()->addSeconds(self::TTL)->getTimestamp(),
        ], now()->addSeconds(self::TTL));

        Log::info('OTP sent', ['mobile' => $mobile, 'code' => $code]);

        return $code;
    }

    /**
     * Seconds remaining before a new code can be requested (0 when none is
     * active).
     */
    public function secondsRemaining(string $mobile): int
    {
        $stored = $this->current($mobile);

        if ($stored === null) {
            return 0;
        }

        return max(0, $stored['expires_at'] - now()->getTimestamp());
    }

    /**
     * @return array{code: string, attempts: int, expires_at: int}|null
     */
    private function current(string $mobile): ?array
    {
        /** @var array{code: string, attempts: int, expires_at: int}|null $stored */
        $stored = Cache::get($this->key($mobile));

        return $stored;
    }

    public static function key(string $mobile): string
    {
        return 'otp:'.$mobile;
    }
}
