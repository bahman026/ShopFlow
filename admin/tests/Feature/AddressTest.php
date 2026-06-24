<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\City;
use App\Models\User;

it('belongs to a user and a city.', function (): void {
    $address = Address::factory()->create();

    expect($address->user)->toBeInstanceOf(User::class)
        ->and($address->city)->toBeInstanceOf(City::class);
});

it('casts prime to boolean.', function (): void {
    $address = Address::factory()->prime()->create();

    expect($address->refresh()->prime)->toBeTrue();
});

it('keeps only one primary address per user.', function (): void {
    $user = User::factory()->create();
    $first = Address::factory()->prime()->create(['user_id' => $user->id]);
    $second = Address::factory()->prime()->create(['user_id' => $user->id]);

    expect($first->refresh()->prime)->toBeFalse()
        ->and($second->refresh()->prime)->toBeTrue();
});

it('does not demote primary addresses of other users.', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $a = Address::factory()->prime()->create(['user_id' => $userA->id]);
    $b = Address::factory()->prime()->create(['user_id' => $userB->id]);

    expect($a->refresh()->prime)->toBeTrue()
        ->and($b->refresh()->prime)->toBeTrue();
});
