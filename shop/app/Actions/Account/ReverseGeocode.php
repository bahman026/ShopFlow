<?php

declare(strict_types=1);

namespace App\Actions\Account;

use Illuminate\Support\Facades\Http;
use Throwable;

class ReverseGeocode
{
    /**
     * Resolve a Neshan formatted address for the given coordinates. Returns null
     * when no service key is configured or the request fails (including timeouts).
     */
    public function __invoke(float $latitude, float $longitude): ?string
    {
        $key = config('services.neshan.service_key');

        if (! is_string($key) || $key === '') {
            return null;
        }

        try {
            $response = Http::withHeaders(['Api-Key' => $key])
                ->timeout(8)
                ->get('https://api.neshan.org/v5/reverse', [
                    'lat' => $latitude,
                    'lng' => $longitude,
                ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $address = $response->json('formatted_address');

        return is_string($address) && $address !== '' ? $address : null;
    }
}
