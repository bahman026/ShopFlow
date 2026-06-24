<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\QueryException;

it('casts autoload to boolean.', function (): void {
    $setting = Setting::factory()->create(['autoload' => true]);

    expect($setting->refresh()->autoload)->toBeTrue();
});

it('enforces a unique key.', function (): void {
    Setting::factory()->create(['key' => 'site_phone']);

    expect(fn () => Setting::factory()->create(['key' => 'site_phone']))
        ->toThrow(QueryException::class);
});
