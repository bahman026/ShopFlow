<?php

declare(strict_types=1);

use App\Enums\GatewayForEnum;
use App\Models\Gateway;

it('casts for and active.', function (): void {
    $gateway = Gateway::factory()->create([
        'for' => GatewayForEnum::USERS,
        'active' => false,
    ]);

    expect($gateway->refresh()->for)->toBe(GatewayForEnum::USERS)
        ->and($gateway->active)->toBeFalse();
});

it('encrypts the password.', function (): void {
    $gateway = Gateway::factory()->create(['password' => 'secret']);

    expect($gateway->refresh()->password)->toBe('secret')
        ->and($gateway->getRawOriginal('password'))->not->toBe('secret');
});

it('can have a polymorphic image that is removed on delete.', function (): void {
    $gateway = Gateway::factory()->withImage()->create();
    $image = $gateway->image;

    expect($image)->not->toBeNull();

    $gateway->delete();

    $this->assertModelMissing($image);
});
