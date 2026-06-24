<?php

declare(strict_types=1);

use App\Filament\Resources\OrderNoteResource;
use App\Models\Order;
use App\Models\OrderNote;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the order note resource.', function () {
    get(OrderNoteResource::getUrl())->assertOk();
});

it('can list order notes in the table.', function () {
    $notes = OrderNote::factory()->count(5)->create();

    livewire(OrderNoteResource\Pages\ListOrderNotes::class)
        ->assertCanSeeTableRecords($notes);
});

it('can render edit order note page.', function () {
    $note = OrderNote::factory()->create();

    get(OrderNoteResource::getUrl('edit', [
        'record' => $note,
    ]))->assertOk();
});

it('can create order note model.', function () {
    $order = Order::factory()->create();
    $user = User::factory()->create();

    livewire(OrderNoteResource\Pages\CreateOrderNote::class)
        ->fillForm([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'content' => 'Customer requested gift wrap.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(OrderNote::class, [
        'order_id' => $order->id,
        'user_id' => $user->id,
        'content' => 'Customer requested gift wrap.',
    ]);
});

it('can delete order note model.', function () {
    $note = OrderNote::factory()->create();

    livewire(OrderNoteResource\Pages\EditOrderNote::class, [
        'record' => $note->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($note);
});
