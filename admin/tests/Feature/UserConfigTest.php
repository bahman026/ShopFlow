<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserConfig;

it('belongs to a user.', function (): void {
    $config = UserConfig::factory()->create();

    expect($config->user)->toBeInstanceOf(User::class);
});

it('casts autoload to boolean.', function (): void {
    $config = UserConfig::factory()->create(['autoload' => true]);

    expect($config->refresh()->autoload)->toBeTrue();
});

it('is deleted when its user is deleted.', function (): void {
    $user = User::factory()->create();
    $config = UserConfig::factory()->create(['user_id' => $user->id]);

    $user->delete();

    $this->assertModelMissing($config);
});
