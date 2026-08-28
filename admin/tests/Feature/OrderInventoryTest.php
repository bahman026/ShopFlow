<?php

declare(strict_types=1);

use App\Enums\OrderStatusEnum;
use App\Exceptions\InsufficientInventoryException;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\Variety;

use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

/**
 * A pending order for `$quantity` of a variety that has `$inventory` in stock.
 *
 * @return array{0: Order, 1: Variety}
 */
function stockOrder(int $inventory = 10, int $quantity = 3, OrderStatusEnum $status = OrderStatusEnum::PENDING): array
{
    $variety = Variety::factory()->create(['inventory' => $inventory]);
    $order = Order::factory()->create(['status' => $status]);

    OrderVariety::create([
        'order_id' => $order->id,
        'product_id' => $variety->product_id,
        'variety_id' => $variety->id,
        'quantity' => $quantity,
        'price' => 1000,
        'final_price' => 1000,
    ]);

    return [$order->refresh(), $variety];
}

it('takes stock when an order becomes paid', function () {
    [$order, $variety] = stockOrder(inventory: 10, quantity: 3);

    $order->update(['status' => OrderStatusEnum::PAID]);

    expect($variety->fresh()->inventory)->toBe(7);
});

it('gives stock back when a paid order is canceled', function () {
    [$order, $variety] = stockOrder(inventory: 10, quantity: 3);

    $order->update(['status' => OrderStatusEnum::PAID]);
    $order->update(['status' => OrderStatusEnum::CANCELED]);

    expect($variety->fresh()->inventory)->toBe(10);
});

it('gives stock back when a delivered order is returned', function () {
    [$order, $variety] = stockOrder(inventory: 10, quantity: 3);

    $order->update(['status' => OrderStatusEnum::PAID]);
    $order->update(['status' => OrderStatusEnum::DELIVERED]);
    $order->update(['status' => OrderStatusEnum::RETURNED]);

    expect($variety->fresh()->inventory)->toBe(10);
});

it('leaves stock alone while an order moves between fulfilment statuses', function () {
    [$order, $variety] = stockOrder(inventory: 10, quantity: 3);

    $order->update(['status' => OrderStatusEnum::PAID]);

    // Paid -> processing -> shipped -> delivered all hold the same stock.
    foreach ([OrderStatusEnum::PROCESSING, OrderStatusEnum::SHIPPED, OrderStatusEnum::DELIVERED] as $status) {
        $order->update(['status' => $status]);
        expect($variety->fresh()->inventory)->toBe(7);
    }
});

it('leaves stock alone when the status does not change', function () {
    [$order, $variety] = stockOrder(inventory: 10, quantity: 3);

    $order->update(['status' => OrderStatusEnum::PAID]);
    $order->update(['content' => 'a note, not a status change']);

    expect($variety->fresh()->inventory)->toBe(7);
});

it('never takes stock twice for the same order', function () {
    [$order, $variety] = stockOrder(inventory: 10, quantity: 3);

    $order->update(['status' => OrderStatusEnum::PAID]);
    $order->update(['status' => OrderStatusEnum::PAID]);

    expect($variety->fresh()->inventory)->toBe(7);
});

it('refuses the transition rather than pushing stock negative', function () {
    [$order, $variety] = stockOrder(inventory: 2, quantity: 3);

    expect(fn () => $order->update(['status' => OrderStatusEnum::PAID]))
        ->toThrow(InsufficientInventoryException::class);

    expect($variety->fresh()->inventory)->toBe(2);
});

it('keeps the order pending when the panel cannot cover the stock', function () {
    [$order, $variety] = stockOrder(inventory: 2, quantity: 3);

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->fillForm(['status' => OrderStatusEnum::PAID->value])
        ->call('save')
        ->assertNotified();

    // Both the status and the stock are untouched — the save rolled back.
    expect($order->fresh()->status)->toBe(OrderStatusEnum::PENDING)
        ->and($variety->fresh()->inventory)->toBe(2);
});
