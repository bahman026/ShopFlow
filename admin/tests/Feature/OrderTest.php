<?php

declare(strict_types=1);

use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;

it('belongs to a user.', function (): void {
    $order = Order::factory()->create();

    expect($order->user)->toBeInstanceOf(User::class);
});

it('casts status, src and for_partner.', function (): void {
    $order = Order::factory()->create([
        'status' => OrderStatusEnum::PAID,
        'src' => OrderSrcEnum::APP,
        'for_partner' => true,
    ]);

    expect($order->refresh()->status)->toBe(OrderStatusEnum::PAID)
        ->and($order->src)->toBe(OrderSrcEnum::APP)
        ->and($order->for_partner)->toBeTrue();
});

it('defaults to a pending status.', function (): void {
    Order::factory()->create(['status' => OrderStatusEnum::PENDING]);

    $this->assertDatabaseHas('orders', ['status' => OrderStatusEnum::PENDING->value]);
});

it('can link an optional coupon.', function (): void {
    $coupon = Coupon::factory()->create();
    $order = Order::factory()->create(['coupon_id' => $coupon->id]);

    expect($order->coupon)->toBeInstanceOf(Coupon::class)
        ->and($order->coupon->id)->toBe($coupon->id);
});

it('keeps the order but nulls user_id when the user is deleted.', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    $user->delete();

    expect($order->refresh()->user_id)->toBeNull();
});

it('auto-generates a unique 10-digit tracking code.', function (): void {
    $order = Order::factory()->create();

    expect($order->tracking_code)->toMatch('/^[1-9]\d{9}$/');
});

it('keeps an explicitly assigned tracking code instead of generating a new one.', function (): void {
    $order = Order::factory()->create(['tracking_code' => '1234567890']);

    expect($order->tracking_code)->toBe('1234567890');
});
