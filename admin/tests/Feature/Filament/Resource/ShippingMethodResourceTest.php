<?php

declare(strict_types=1);

use App\Enums\ShippingMethodForEnum;
use App\Filament\Resources\ShippingMethodResource;
use App\Models\ShippingLine;
use App\Models\ShippingMethod;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the shipping method resource.', function () {
    get(ShippingMethodResource::getUrl())->assertOk();
});

it('can list shipping methods in the table.', function () {
    $methods = ShippingMethod::factory()->count(5)->create();

    livewire(ShippingMethodResource\Pages\ListShippingMethods::class)
        ->assertCanSeeTableRecords($methods);
});

it('can render edit page.', function () {
    $method = ShippingMethod::factory()->create();

    get(ShippingMethodResource::getUrl('edit', [
        'record' => $method,
    ]))->assertOk();
});

it('can create shipping method.', function () {
    $line = ShippingLine::factory()->create();

    livewire(ShippingMethodResource\Pages\CreateShippingMethod::class)
        ->fillForm([
            'shipping_line_id' => $line->id,
            'name' => 'Standard Post',
            'type' => 'Economy',
            'for' => ShippingMethodForEnum::CUSTOMER->value,
            'status' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(ShippingMethod::class, [
        'name' => 'Standard Post',
        'shipping_line_id' => $line->id,
    ]);
});

it('can update shipping method.', function () {
    $method = ShippingMethod::factory()->create();
    $new = ShippingMethod::factory()->make(['shipping_line_id' => $method->shipping_line_id]);

    livewire(ShippingMethodResource\Pages\EditShippingMethod::class, [
        'record' => $method->getRouteKey(),
    ])
        ->fillForm([
            'shipping_line_id' => $new->shipping_line_id,
            'name' => $new->name,
            'for' => ShippingMethodForEnum::CUSTOMER->value,
            'status' => $new->status,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($method->refresh())
        ->name->toBe($new->name);
});

it('can delete shipping method.', function () {
    $method = ShippingMethod::factory()->create();

    livewire(ShippingMethodResource\Pages\EditShippingMethod::class, [
        'record' => $method->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($method);
});
