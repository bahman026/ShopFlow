<?php

declare(strict_types=1);

use App\Enums\OrderShippingPaymentTypeEnum;
use App\Models\Order;
use App\Models\OrderShipping;

it('belongs to an order.', function (): void {
    $shipping = OrderShipping::factory()->create();

    expect($shipping->order)->toBeInstanceOf(Order::class);
});

it('casts payment_type and cheque_is_require.', function (): void {
    $shipping = OrderShipping::factory()->create([
        'payment_type' => OrderShippingPaymentTypeEnum::ONLINE,
        'cheque_is_require' => true,
    ]);

    expect($shipping->refresh()->payment_type)->toBe(OrderShippingPaymentTypeEnum::ONLINE)
        ->and($shipping->cheque_is_require)->toBeTrue();
});

it('is deleted when its order is deleted.', function (): void {
    $shipping = OrderShipping::factory()->create();

    $shipping->order->delete();

    $this->assertModelMissing($shipping);
});

it('is exposed through the order relation.', function (): void {
    $order = Order::factory()->create();
    OrderShipping::factory()->count(2)->create(['order_id' => $order->id]);

    expect($order->orderShippings)->toHaveCount(2);
});
