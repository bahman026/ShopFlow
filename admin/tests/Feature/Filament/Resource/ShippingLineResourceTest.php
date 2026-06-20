<?php

declare(strict_types=1);

use App\Filament\Resources\ShippingLineResource;
use App\Models\ShippingLine;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the shipping line resource.', function () {
    get(ShippingLineResource::getUrl())->assertOk();
});

it('can list shipping lines in the table.', function () {
    $lines = ShippingLine::factory()->count(5)->create();

    livewire(ShippingLineResource\Pages\ListShippingLines::class)
        ->assertCanSeeTableRecords($lines);
});

it('can render edit page.', function () {
    $line = ShippingLine::factory()->create();

    get(ShippingLineResource::getUrl('edit', [
        'record' => $line,
    ]))->assertOk();
});

it('can create shipping line.', function () {
    $new = ShippingLine::factory()->make();

    livewire(ShippingLineResource\Pages\CreateShippingLine::class)
        ->fillForm([
            'name' => $new->name,
            'cost' => $new->cost,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(ShippingLine::class, [
        'name' => $new->name,
        'cost' => $new->cost,
    ]);
});

it('can update shipping line.', function () {
    $line = ShippingLine::factory()->create();
    $new = ShippingLine::factory()->make();

    livewire(ShippingLineResource\Pages\EditShippingLine::class, [
        'record' => $line->getRouteKey(),
    ])
        ->fillForm([
            'name' => $new->name,
            'cost' => $new->cost,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($line->refresh())
        ->name->toBe($new->name)
        ->cost->toBe($new->cost);
});

it('can delete shipping line.', function () {
    $line = ShippingLine::factory()->create();

    livewire(ShippingLineResource\Pages\EditShippingLine::class, [
        'record' => $line->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($line);
});
