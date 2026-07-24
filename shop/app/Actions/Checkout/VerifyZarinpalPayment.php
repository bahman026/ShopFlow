<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Support\Currency;
use Illuminate\Support\Facades\Http;
use Throwable;

class VerifyZarinpalPayment
{
    /**
     * Confirm a completed payment with Zarinpal. `code` 100 (first
     * verification) and 101 (already verified) both mean success — the
     * caller should treat both as an idempotent success. Returns null when
     * unavailable (missing config or the request itself failed).
     *
     * @return array{code: int, refId: string|null, cardPan: string|null}|null
     */
    public function __invoke(string $authority, int $amountToman): ?array
    {
        $merchantId = config('services.zarinpal.merchant_id');

        if (! is_string($merchantId) || $merchantId === '') {
            return null;
        }

        try {
            $response = Http::timeout(10)->post($this->baseUrl().'/pg/v4/payment/verify.json', [
                'merchant_id' => $merchantId,
                'amount' => Currency::tomanToRial($amountToman),
                'authority' => $authority,
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $code = $response->json('data.code');

        if (! is_int($code)) {
            return null;
        }

        $refId = $response->json('data.ref_id');
        $cardPan = $response->json('data.card_pan');

        return [
            'code' => $code,
            'refId' => is_string($refId) || is_int($refId) ? (string) $refId : null,
            'cardPan' => is_string($cardPan) ? $cardPan : null,
        ];
    }

    private function baseUrl(): string
    {
        $baseUrl = config('services.zarinpal.base_url');

        return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : 'https://sandbox.zarinpal.com';
    }
}
