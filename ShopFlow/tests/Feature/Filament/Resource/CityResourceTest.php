<?php

declare(strict_types=1);

use App\Filament\Resources\CityResource;
use App\Models\City;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the city resource.', function () {
    // Act & Assert
    get(CityResource::getUrl())->assertOk();
});

it('can list cities in the table.', function () {
    // Arrange
    $cities = City::factory()->count(5)->create();

    // Act & Assert
    livewire(CityResource\Pages\ListCities::class)
        ->assertCanSeeTableRecords($cities);
});

it('can render edit city page.', function () {
    // Act & Assert
    get(CityResource::getUrl('edit', [
        'record' => City::factory()->create(),
    ]))
        ->assertSuccessful();
});

it('can update city model.', function () {
    // Arrange
    $city = City::factory()->create();
    $newCity = City::factory()->make();

    // Act & Assert
    livewire(CityResource\Pages\EditCity::class, [
        'record' => $city->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newCity->name,
            'province_id' => $newCity->province_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    //    dd($newCity)
    expect($city->refresh())
        ->name->toBe($newCity->name)
        ->province_id->toBe($newCity->province_id);
});

it('can create city model.', function () {
    // Arrange
    $newCity = City::factory()->make();

    // Act & Assert
    livewire(CityResource\Pages\CreateCity::class)
        ->fillForm([
            'name' => $newCity->name,
            'province_id' => $newCity->province_id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(City::class, [
        'name' => $newCity->name,
        'province_id' => $newCity->province_id,
    ]);
});

it('can delete city model.', function () {
    // Arrange
    $city = City::factory()->create();

    // Act & Assert
    livewire(CityResource\Pages\EditCity::class, [
        'record' => $city->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);
    $this->assertModelMissing($city);
});
