<?php

declare(strict_types=1);

use App\Enums\GatewayForEnum;
use App\Filament\Resources\GatewayResource;
use App\Models\Gateway;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the gateway resource.', function () {
    get(GatewayResource::getUrl())->assertOk();
});

it('can list gateways in the table.', function () {
    $gateways = Gateway::factory()->count(5)->create();

    livewire(GatewayResource\Pages\ListGateways::class)
        ->assertCanSeeTableRecords($gateways);
});

it('can render edit gateway page.', function () {
    $gateway = Gateway::factory()->create();

    get(GatewayResource::getUrl('edit', [
        'record' => $gateway,
    ]))->assertOk();
});

it('can create gateway model.', function () {
    livewire(GatewayResource\Pages\CreateGateway::class)
        ->fillForm([
            'name' => 'Mellat Gateway',
            'key' => 'mellat',
            'for' => GatewayForEnum::EVERYONE->value,
            'priority' => 10,
            'active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Gateway::class, [
        'name' => 'Mellat Gateway',
        'key' => 'mellat',
        'for' => GatewayForEnum::EVERYONE->value,
        'priority' => 10,
    ]);
});

it('can delete gateway model.', function () {
    $gateway = Gateway::factory()->create();

    livewire(GatewayResource\Pages\EditGateway::class, [
        'record' => $gateway->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($gateway);
});
