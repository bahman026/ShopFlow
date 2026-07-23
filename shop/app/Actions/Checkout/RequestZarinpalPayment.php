<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Support\Currency;
use Illuminate\Support\Facades\Http;
use Throwable;

class RequestZarinpalPayment
{
    /**
     * Ask Zarinpal to open a payment session for this order. Returns the
     * authority to redirect the customer to, or null when unavailable
     * (missing config or the gateway rejected/failed the request).
     *
     * @return array{authority: string}|null
     */
    public function __invoke(int $amountToman, string $description, string $callbackUrl, ?string $mobile, ?string $email): ?array
    {
        $merchantId = config('services.zarinpal.merchant_id');

        if (! is_string($merchantId) || $merchantId === '') {
            return null;
        }

        try {
            $response = Http::timeout(10)->post($this->baseUrl().'/pg/v4/payment/request.json', [
                'merchant_id' => $merchantId,
                'amount' => Currency::tomanToRial($amountToman),
                'callback_url' => $callbackUrl,
                'description' => $description,
                'metadata' => array_filter(['mobile' => $mobile, 'email' => $email]),
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $code = $response->json('data.code');
        $authority = $response->json('data.authority');

        if ($code !== 100 || ! is_string($authority) || $authority === '') {
            return null;
        }

        return ['authority' => $authority];
    }

    public function startPayUrl(string $authority): string
    {
        return $this->baseUrl().'/pg/StartPay/'.$authority;
    }

    private function baseUrl(): string
    {
        $baseUrl = config('services.zarinpal.base_url');

        return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : 'https://sandbox.zarinpal.com';
    }
}
