<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\Product;
use App\Models\Variety;

it('belongs to an order, product and variety.', function (): void {
    $item = OrderVariety::factory()->create();

    expect($item->order)->toBeInstanceOf(Order::class)
        ->and($item->product)->toBeInstanceOf(Product::class)
        ->and($item->variety)->toBeInstanceOf(Variety::class);
});

it('casts quantity to an integer.', function (): void {
    $item = OrderVariety::factory()->create(['quantity' => 4]);

    expect($item->refresh()->quantity)->toBe(4);
});

it('is deleted when its order is deleted.', function (): void {
    $item = OrderVariety::factory()->create();

    $item->order->delete();

    $this->assertModelMissing($item);
});

it('exposes order line items through the order.', function (): void {
    $order = Order::factory()->create();
    OrderVariety::factory()->count(3)->create(['order_id' => $order->id]);

    expect($order->orderVarieties)->toHaveCount(3);
});
