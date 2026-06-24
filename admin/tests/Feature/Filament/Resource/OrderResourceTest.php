<?php

declare(strict_types=1);

use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the order resource.', function () {
    get(OrderResource::getUrl())->assertOk();
});

it('can list orders in the table.', function () {
    $orders = Order::factory()->count(5)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->assertCanSeeTableRecords($orders);
});

it('can render edit order page.', function () {
    $order = Order::factory()->create();

    get(OrderResource::getUrl('edit', [
        'record' => $order,
    ]))->assertOk();
});

it('can update order model.', function () {
    $order = Order::factory()->create();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'status' => OrderStatusEnum::PAID->value,
            'src' => OrderSrcEnum::APP->value,
            'for_partner' => true,
            'total_products_price' => 1_000_000,
            'discount' => 0,
            'coupon_discount' => 0,
            'tax' => 90_000,
            'shipping_cost' => 50_000,
            'total_price' => 1_140_000,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh())
        ->status->toBe(OrderStatusEnum::PAID)
        ->src->toBe(OrderSrcEnum::APP)
        ->for_partner->toBeTrue();
});

it('can create order model.', function () {
    livewire(OrderResource\Pages\CreateOrder::class)
        ->fillForm([
            'status' => OrderStatusEnum::PENDING->value,
            'src' => OrderSrcEnum::WEB->value,
            'for_partner' => false,
            'total_products_price' => 2_000_000,
            'discount' => 0,
            'coupon_discount' => 0,
            'tax' => 180_000,
            'shipping_cost' => 0,
            'total_price' => 2_180_000,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Order::class, [
        'status' => OrderStatusEnum::PENDING->value,
        'src' => OrderSrcEnum::WEB->value,
        'for_partner' => false,
        'total_products_price' => 2_000_000,
        'total_price' => 2_180_000,
    ]);
});

it('can delete order model.', function () {
    $order = Order::factory()->create();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($order);
});
