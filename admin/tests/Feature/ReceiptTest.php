<?php

declare(strict_types=1);

use App\Enums\ReceiptTypeEnum;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;

it('belongs to a user.', function (): void {
    $receipt = Receipt::factory()->create();

    expect($receipt->user)->toBeInstanceOf(User::class);
});

it('casts type, is_paya and paid_at.', function (): void {
    $receipt = Receipt::factory()->create([
        'type' => ReceiptTypeEnum::PREPAYMENT,
        'is_paya' => true,
        'paid_at' => now(),
    ]);

    expect($receipt->refresh()->type)->toBe(ReceiptTypeEnum::PREPAYMENT)
        ->and($receipt->is_paya)->toBeTrue()
        ->and($receipt->paid_at)->not->toBeNull();
});

it('can optionally link an order.', function (): void {
    $order = Order::factory()->create();
    $receipt = Receipt::factory()->create(['order_id' => $order->id]);

    expect($receipt->order)->toBeInstanceOf(Order::class)
        ->and($receipt->order->id)->toBe($order->id);
});

it('can have a polymorphic image that is removed on delete.', function (): void {
    $receipt = Receipt::factory()->withImage()->create();
    $image = $receipt->image;

    expect($image)->not->toBeNull();

    $receipt->delete();

    $this->assertModelMissing($image);
});

it('keeps the receipt but nulls order_id when the order is deleted.', function (): void {
    $order = Order::factory()->create();
    $receipt = Receipt::factory()->create(['order_id' => $order->id]);

    $order->delete();

    expect($receipt->refresh()->order_id)->toBeNull();
});
