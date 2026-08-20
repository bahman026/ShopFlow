<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\SmsSender;
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

    public function __construct(private SmsSender $sms) {}

    /**
     * Generate a one-time code, send it, and store it for verification.
     *
     * It is idempotent within the validity window: while an unexpired code
     * exists it is reused (no new code, no SMS, no extended lifetime), so
     * repeated requests cannot reset the expiry — and a resend costs nothing.
     *
     * Returns null when the provider would not take the message. The code is
     * deliberately **sent before it is stored**: storing one the customer never
     * receives would lock them out for the whole TTL, unable to ask again,
     * waiting for an SMS that does not exist. Nothing is cached on failure, so
     * the next attempt starts clean.
     *
     * The active code is returned so it can be surfaced in non-production
     * environments for testing.
     */
    public function __invoke(string $mobile): ?string
    {
        $existing = $this->current($mobile);

        if ($existing !== null && $existing['expires_at'] > now()->getTimestamp()) {
            return $existing['code'];
        }

        $code = str_pad((string) random_int(0, 10 ** self::LENGTH - 1), self::LENGTH, '0', STR_PAD_LEFT);

        // One instant, used for the SMS text, the stored expiry and the cache
        // lifetime alike, so the time the customer reads cannot drift from the
        // moment the code actually stops working.
        $expiresAt = now()->addSeconds(self::TTL);

        // Testing bypass: a configured fixed code is stored without sending
        // anything, for the window where the provider accepts a send and
        // delivers nothing. See config/otp.php — this is an authentication
        // bypass and must be off wherever real customers log in.
        $fixed = $this->fixedCodeFor($mobile);

        if ($fixed !== null) {
            $code = $fixed;
        } elseif (! $this->sms->sendVerificationCode($mobile, $code, $expiresAt)) {
            return null;
        }

        Cache::put($this->key($mobile), [
            'code' => $code,
            'attempts' => 0,
            'expires_at' => $expiresAt->getTimestamp(),
        ], $expiresAt);

        return $code;
    }

    /**
     * The fixed code configured for this mobile, or null when the real flow
     * applies.
     *
     * Every hit is logged, because a bypass nobody can see is a bypass nobody
     * remembers to remove: `warning` when it is scoped to specific numbers,
     * `critical` when no allowlist is set and therefore every number on the
     * site can be signed into with one known code.
     */
    private function fixedCodeFor(string $mobile): ?string
    {
        $fixed = config('otp.fixed_code');

        if (! is_string($fixed) || $fixed === '') {
            return null;
        }

        /** @var array<int, string> $allowed */
        $allowed = config('otp.fixed_mobiles', []);

        if ($allowed !== [] && ! in_array($mobile, $allowed, true)) {
            return null;
        }

        $allowed === []
            ? Log::critical('OTP fixed code is active for EVERY mobile — anyone can sign in as anyone', ['mobile' => $mobile])
            : Log::warning('OTP fixed code used instead of sending an SMS', ['mobile' => $mobile]);

        return $fixed;
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
