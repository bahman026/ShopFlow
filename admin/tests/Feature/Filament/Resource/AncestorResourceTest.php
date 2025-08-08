<?php

declare(strict_types=1);

use App\Filament\Resources\AncestorResource;
use App\Models\Ancestor;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the ancestor resource.', function () {
    // Act & Assert
    get(AncestorResource::getUrl())->assertOk();
});

it('can list ancestors in the table.', function () {
    // Arrange
    $ancestors = Ancestor::factory()->count(5)->create();

    // Act & Assert
    livewire(AncestorResource\Pages\ListAncestors::class)
        ->assertCanSeeTableRecords($ancestors);
});

it('can render edit ancestor page.', function () {
    // Act & Assert
    get(AncestorResource::getUrl('edit', [
        'record' => Ancestor::factory()->create(),
    ]))
        ->assertSuccessful();
});

it('can update ancestor model.', function () {
    // Arrange
    $ancestor = Ancestor::factory()->create();
    $newAncestor = Ancestor::factory()->make();

    // Act & Assert
    livewire(AncestorResource\Pages\EditAncestor::class, [
        'record' => $ancestor->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newAncestor->name,
            'order' => $newAncestor->order,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($ancestor->refresh())
        ->name->toBe($newAncestor->name)
        ->order->toBe($newAncestor->order);

});

it('can create ancestor model.', function () {
    // Arrange
    $newAncestor = Ancestor::factory()->make();

    livewire(AncestorResource\Pages\CreateAncestor::class)
        ->fillForm([
            'name' => $newAncestor->name,
            'order' => $newAncestor->order,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    //     Assert the database has the expected data
    $this->assertDatabaseHas(Ancestor::class, [
        'name' => $newAncestor->name,
        'order' => $newAncestor->order,
    ]);

});

it('cannot delete ancestor model.', function () {
    // Arrange
    $ancestor = Ancestor::factory()->create();

    // Act & Assert
    livewire(AncestorResource\Pages\EditAncestor::class, [
        'record' => $ancestor->getRouteKey(),
    ])
        ->assertActionDoesNotExist(DeleteAction::class);
});
