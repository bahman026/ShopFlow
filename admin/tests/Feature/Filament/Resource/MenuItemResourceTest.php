<?php

declare(strict_types=1);

use App\Filament\Resources\MenuItemResource;
use App\Models\Menu;
use App\Models\MenuItem;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the menu item resource.', function () {
    get(MenuItemResource::getUrl())->assertOk();
});

it('can list menu items in the table.', function () {
    $items = MenuItem::factory()->count(5)->create();

    livewire(MenuItemResource\Pages\ListMenuItems::class)
        ->assertCanSeeTableRecords($items);
});

it('can render edit menu item page.', function () {
    $item = MenuItem::factory()->create();

    get(MenuItemResource::getUrl('edit', ['record' => $item]))->assertOk();
});

it('can update menu item model.', function () {
    $item = MenuItem::factory()->create();
    $newItem = MenuItem::factory()->make(['menu_id' => $item->menu_id]);

    livewire(MenuItemResource\Pages\EditMenuItem::class, ['record' => $item->getRouteKey()])
        ->fillForm([
            'menu_id' => $item->menu_id,
            'name' => $newItem->name,
            'url' => $newItem->url,
            'label' => $newItem->label,
            'order' => $newItem->order,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($item->refresh())
        ->name->toBe($newItem->name)
        ->order->toBe($newItem->order);
});

it('can create menu item model.', function () {
    $menu = Menu::factory()->create();
    $newItem = MenuItem::factory()->make(['menu_id' => $menu->id]);

    livewire(MenuItemResource\Pages\CreateMenuItem::class)
        ->fillForm([
            'menu_id' => $menu->id,
            'name' => $newItem->name,
            'url' => $newItem->url,
            'label' => $newItem->label,
            'order' => $newItem->order,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(MenuItem::class, [
        'menu_id' => $menu->id,
        'name' => $newItem->name,
    ]);
});

it('can delete menu item model.', function () {
    $item = MenuItem::factory()->create();

    livewire(MenuItemResource\Pages\EditMenuItem::class, ['record' => $item->getRouteKey()])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($item);
});

it('cascades delete to children when menu item is deleted.', function () {
    $parent = MenuItem::factory()->create();
    $child = MenuItem::factory()->withParent($parent)->create();

    $parent->delete();

    $this->assertModelMissing($child);
});

it('cascades delete image when menu item is deleted.', function () {
    $item = MenuItem::factory()->create();
    $image = $item->image()->create([
        'path' => 'test.jpg',
        'is_featured' => true,
        'order' => 0,
        'alt_text' => null,
    ]);

    $item->delete();

    $this->assertModelMissing($image);
});
