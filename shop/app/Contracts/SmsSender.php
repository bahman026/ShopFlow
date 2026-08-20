<?php

declare(strict_types=1);

namespace App\Contracts;

use DateTimeInterface;

interface SmsSender
{
    /**
     * Deliver a one-time verification code to a mobile number.
     *
     * `$expiresAt` is the moment the code stops working. The caller owns that
     * instant so the text the customer reads and the code's real lifetime can
     * never disagree — telling someone a code lasts until 14:35 when it dies at
     * 14:33 is worse than saying nothing.
     *
     * Returns false when the code could not be handed to the provider, so the
     * caller can avoid storing a code the customer will never receive. An
     * implementation must not throw for an ordinary delivery failure (network,
     * no credit, rejected template) — those are a `false`, not an exception.
     *
     * Swap the bound implementation without touching callers; production sends
     * over sms.ir, local and CI only log (see AppServiceProvider).
     */
    public function sendVerificationCode(string $mobile, string $code, DateTimeInterface $expiresAt): bool;
}
