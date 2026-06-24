<?php

declare(strict_types=1);

use App\Filament\Resources\OrderVarietyResource;
use App\Models\Order;
use App\Models\OrderVariety;
use App\Models\Variety;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the order variety resource.', function () {
    get(OrderVarietyResource::getUrl())->assertOk();
});

it('can list order varieties in the table.', function () {
    $items = OrderVariety::factory()->count(5)->create();

    livewire(OrderVarietyResource\Pages\ListOrderVarieties::class)
        ->assertCanSeeTableRecords($items);
});

it('can render edit order variety page.', function () {
    $item = OrderVariety::factory()->create();

    get(OrderVarietyResource::getUrl('edit', [
        'record' => $item,
    ]))->assertOk();
});

it('can update order variety model.', function () {
    $item = OrderVariety::factory()->create();

    livewire(OrderVarietyResource\Pages\EditOrderVariety::class, [
        'record' => $item->getRouteKey(),
    ])
        ->fillForm([
            'quantity' => 7,
            'gather_quantity' => 0,
            'invoice_quantity' => 0,
            'price' => 500_000,
            'discount' => 0,
            'coupon_discount' => 0,
            'final_price' => 3_500_000,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($item->refresh()->quantity)->toBe(7);
    $this->assertDatabaseHas(OrderVariety::class, [
        'id' => $item->id,
        'price' => 500_000,
        'final_price' => 3_500_000,
    ]);
});

it('can create order variety model.', function () {
    $order = Order::factory()->create();
    $variety = Variety::factory()->create();

    livewire(OrderVarietyResource\Pages\CreateOrderVariety::class)
        ->fillForm([
            'order_id' => $order->id,
            'product_id' => $variety->product_id,
            'variety_id' => $variety->id,
            'quantity' => 2,
            'gather_quantity' => 0,
            'invoice_quantity' => 0,
            'price' => 1_000_000,
            'discount' => 0,
            'coupon_discount' => 0,
            'final_price' => 2_000_000,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(OrderVariety::class, [
        'order_id' => $order->id,
        'variety_id' => $variety->id,
        'quantity' => 2,
        'final_price' => 2_000_000,
    ]);
});

it('can delete order variety model.', function () {
    $item = OrderVariety::factory()->create();

    livewire(OrderVarietyResource\Pages\EditOrderVariety::class, [
        'record' => $item->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($item);
});
