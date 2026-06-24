<?php

declare(strict_types=1);

use App\Filament\Resources\ShippingCityResource;
use App\Models\ShippingCity;
use App\Models\ShippingMethod;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the shipping city resource.', function () {
    get(ShippingCityResource::getUrl())->assertOk();
});

it('can list shipping cities in the table.', function () {
    $cities = ShippingCity::factory()->count(5)->create();

    livewire(ShippingCityResource\Pages\ListShippingCities::class)
        ->assertCanSeeTableRecords($cities);
});

it('can render edit page.', function () {
    $city = ShippingCity::factory()->create();

    get(ShippingCityResource::getUrl('edit', [
        'record' => $city,
    ]))->assertOk();
});

it('can create shipping city.', function () {
    $method = ShippingMethod::factory()->create();

    livewire(ShippingCityResource\Pages\CreateShippingCity::class)
        ->fillForm([
            'shipping_method_id' => $method->id,
            'amount' => 50000,
            'pay_on_delivery' => false,
            'status' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(ShippingCity::class, [
        'shipping_method_id' => $method->id,
        'amount' => 50000,
    ]);
});

it('can update shipping city.', function () {
    $city = ShippingCity::factory()->create();

    livewire(ShippingCityResource\Pages\EditShippingCity::class, [
        'record' => $city->getRouteKey(),
    ])
        ->fillForm([
            'shipping_method_id' => $city->shipping_method_id,
            'amount' => 75000,
            'pay_on_delivery' => false,
            'status' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($city->refresh())
        ->amount->toBe(75000)
        ->status->toBeFalse();
});

it('can delete shipping city.', function () {
    $city = ShippingCity::factory()->create();

    livewire(ShippingCityResource\Pages\EditShippingCity::class, [
        'record' => $city->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($city);
});

it('cascades delete when shipping method is deleted.', function () {
    $city = ShippingCity::factory()->create();
    $method = $city->shippingMethod;

    $method->delete();

    $this->assertModelMissing($city);
});
