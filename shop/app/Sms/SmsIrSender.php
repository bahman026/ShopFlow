<?php

declare(strict_types=1);

namespace App\Sms;

use App\Contracts\SmsSender;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsIrSender implements SmsSender
{
    /**
     * The timezone the expiry time is written in.
     *
     * Deliberately not `config('app.timezone')`, which is UTC: the app stores
     * timestamps in UTC and must keep doing so, but a customer in Iran reading
     * "valid until 11:05" when their own clock says 14:35 would simply think
     * the code had already expired.
     */
    private const DISPLAY_TIMEZONE = 'Asia/Tehran';

    /**
     * Sends the OTP through sms.ir's `send/verify` endpoint.
     *
     * `send/verify` rather than `send/bulk` on purpose: verify messages go out
     * over sms.ir's shared service line, which needs no rented number, reaches
     * customers who blocked advertising SMS at their operator, and has no
     * night-time restriction. A login code sent as a bulk message would be
     * silently dropped for some customers and delayed for others.
     *
     * The template lives in the sms.ir panel and is referenced by id; only the
     * parameter values travel with the request. Every placeholder in the
     * template must get a value here — a missing one is not left blank, it
     * reaches the customer as the literal `#expireDate#`.
     */
    public function sendVerificationCode(string $mobile, string $code, DateTimeInterface $expiresAt): bool
    {
        $apiKey = config('services.sms_ir.api_key');
        $templateId = config('services.sms_ir.template_id');

        if (! is_string($apiKey) || $apiKey === '' || ! is_numeric($templateId)) {
            Log::warning('sms.ir is not configured; OTP not sent', ['mobile' => $mobile]);

            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['x-api-key' => $apiKey])
                ->acceptJson()
                ->post($this->baseUrl().'/v1/send/verify', [
                    'mobile' => $mobile,
                    'templateId' => (int) $templateId,
                    // Names match the panel template's placeholders exactly —
                    // `#code#` and `#expireDate#`, both lower-camel. Values over
                    // 25 characters are rejected with status 114, which is why
                    // the expiry is a bare clock time and not a full date.
                    'parameters' => [
                        ['name' => 'code', 'value' => $code],
                        ['name' => 'expireDate', 'value' => $this->expiryForHumans($expiresAt)],
                    ],
                ]);
        } catch (Throwable $exception) {
            Log::error('sms.ir request failed', ['mobile' => $mobile, 'error' => $exception->getMessage()]);

            return false;
        }

        // A non-1 status is a real refusal even on HTTP 200 (113 unknown
        // template, 114 parameter too long, 116 empty parameter name), so the
        // body decides, not the status line.
        if (! $response->successful() || $response->json('status') !== 1) {
            Log::error('sms.ir rejected the OTP', [
                'mobile' => $mobile,
                'http' => $response->status(),
                'status' => $response->json('status'),
                'message' => $response->json('message'),
            ]);

            return false;
        }

        return true;
    }

    /**
     * The expiry as the customer's own wall clock, e.g. `14:35`.
     */
    private function expiryForHumans(DateTimeInterface $expiresAt): string
    {
        return Carbon::instance($expiresAt)
            ->setTimezone(self::DISPLAY_TIMEZONE)
            ->format('H:i');
    }

    private function baseUrl(): string
    {
        $baseUrl = config('services.sms_ir.base_url');

        return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : 'https://api.sms.ir';
    }
}
