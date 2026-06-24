<?php

declare(strict_types=1);

use App\Enums\OrderShippingPaymentTypeEnum;
use App\Filament\Resources\OrderShippingResource;
use App\Models\Order;
use App\Models\OrderShipping;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the order shipping resource.', function () {
    get(OrderShippingResource::getUrl())->assertOk();
});

it('can list order shippings in the table.', function () {
    $shippings = OrderShipping::factory()->count(5)->create();

    livewire(OrderShippingResource\Pages\ListOrderShippings::class)
        ->assertCanSeeTableRecords($shippings);
});

it('can render edit order shipping page.', function () {
    $shipping = OrderShipping::factory()->create();

    get(OrderShippingResource::getUrl('edit', [
        'record' => $shipping,
    ]))->assertOk();
});

it('can create order shipping model.', function () {
    $order = Order::factory()->create();

    livewire(OrderShippingResource\Pages\CreateOrderShipping::class)
        ->fillForm([
            'order_id' => $order->id,
            'payment_type' => OrderShippingPaymentTypeEnum::POS->value,
            'pack_count' => 2,
            'cheque_is_require' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(OrderShipping::class, [
        'order_id' => $order->id,
        'payment_type' => OrderShippingPaymentTypeEnum::POS->value,
        'pack_count' => 2,
    ]);
});

it('can delete order shipping model.', function () {
    $shipping = OrderShipping::factory()->create();

    livewire(OrderShippingResource\Pages\EditOrderShipping::class, [
        'record' => $shipping->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($shipping);
});
