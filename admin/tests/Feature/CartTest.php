<?php

declare(strict_types=1);

use App\Models\Cart;
use App\Models\User;
use App\Models\Variety;

it('belongs to a user and a variety.', function (): void {
    $cart = Cart::factory()->create();

    expect($cart->user)->toBeInstanceOf(User::class)
        ->and($cart->variety)->toBeInstanceOf(Variety::class);
});

it('casts count to an integer.', function (): void {
    $cart = Cart::factory()->create(['count' => 3]);

    expect($cart->refresh()->count)->toBe(3);
});

it('creates a guest cart with a session and no user.', function (): void {
    $cart = Cart::factory()->guest()->create();

    expect($cart->user_id)->toBeNull()
        ->and($cart->session_id)->not->toBeNull();
});

it('deletes carts when the user is deleted.', function (): void {
    $user = User::factory()->create();
    $cart = Cart::factory()->for($user)->create();

    $user->delete();

    expect(Cart::query()->whereKey($cart->id)->exists())->toBeFalse();
});
