<?php

declare(strict_types=1);

namespace App\Sms;

use App\Contracts\SmsSender;
use DateTimeInterface;
use Illuminate\Support\Facades\Log;

class LogSmsSender implements SmsSender
{
    /**
     * Writes the code to the log instead of sending it, for local development
     * and CI — no credit is spent and no real phone is needed.
     *
     * This is the binding whenever `services.sms_ir.api_key` is empty, so a
     * clone with no credentials works out of the box and production cannot end
     * up here by accident: filling the key is what switches it over.
     */
    public function sendVerificationCode(string $mobile, string $code, DateTimeInterface $expiresAt): bool
    {
        Log::info('OTP sent (log driver)', [
            'mobile' => $mobile,
            'code' => $code,
            'expires_at' => $expiresAt->format('c'),
        ]);

        return true;
    }
}
