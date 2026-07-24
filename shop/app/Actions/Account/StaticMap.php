<?php

declare(strict_types=1);

namespace App\Actions\Account;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class StaticMap
{
    /**
     * Fetch a Neshan static map image for the given coordinates. Proxied so the
     * service key never reaches the browser, and cached so repeated pans/zooms
     * don't re-hit Neshan (which is slow and occasionally times out). Returns
     * the raw image bytes and content type, or null when unavailable.
     *
     * @return array{body: string, contentType: string}|null
     */
    public function __invoke(float $latitude, float $longitude, int $zoom): ?array
    {
        $key = config('services.neshan.service_key');

        if (! is_string($key) || $key === '') {
            return null;
        }

        $cacheKey = sprintf('neshan:static:%.5f:%.5f:%d', $latitude, $longitude, $zoom);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            /** @var array{body: string, contentType: string} $cached */
            return $cached;
        }

        try {
            $response = Http::timeout(8)->retry(2, 200)->get('https://api.neshan.org/v5/static', [
                'key' => $key,
                'type' => 'standard-day',
                'zoom' => $zoom,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'width' => 600,
                'height' => 350,
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $contentType = $response->header('Content-Type');

        $image = [
            'body' => $response->body(),
            'contentType' => $contentType !== '' ? $contentType : 'image/png',
        ];

        Cache::put($cacheKey, $image, now()->addDays(30));

        return $image;
    }
}
