<?php

declare(strict_types=1);

use App\Filament\Resources\MenuResource;
use App\Models\Menu;
use App\Models\MenuItem;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the menu resource.', function () {
    get(MenuResource::getUrl())->assertOk();
});

it('can list menus in the table.', function () {
    $menus = Menu::factory()->count(5)->create();

    livewire(MenuResource\Pages\ListMenus::class)
        ->assertCanSeeTableRecords($menus);
});

it('can render edit menu page.', function () {
    $menu = Menu::factory()->create();

    get(MenuResource::getUrl('edit', ['record' => $menu]))->assertOk();
});

it('can update menu model.', function () {
    $menu = Menu::factory()->create();
    $newMenu = Menu::factory()->make();

    livewire(MenuResource\Pages\EditMenu::class, ['record' => $menu->getRouteKey()])
        ->fillForm([
            'name' => $newMenu->name,
            'position' => $newMenu->position,
            'status' => $newMenu->status,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($menu->refresh())
        ->name->toBe($newMenu->name)
        ->position->toBe($newMenu->position)
        ->status->toBe($newMenu->status);
});

it('can create menu model.', function () {
    $newMenu = Menu::factory()->make();

    livewire(MenuResource\Pages\CreateMenu::class)
        ->fillForm([
            'name' => $newMenu->name,
            'position' => $newMenu->position,
            'status' => $newMenu->status,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Menu::class, [
        'name' => $newMenu->name,
        'position' => $newMenu->position,
    ]);
});

it('can delete menu model.', function () {
    $menu = Menu::factory()->create();

    livewire(MenuResource\Pages\EditMenu::class, ['record' => $menu->getRouteKey()])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($menu);
});

it('cascades delete to menu items when menu is deleted.', function () {
    $menu = Menu::factory()->create();
    $item = MenuItem::factory()->create(['menu_id' => $menu->id]);

    $menu->delete();

    $this->assertModelMissing($item);
});
