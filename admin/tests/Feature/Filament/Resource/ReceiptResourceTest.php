<?php

declare(strict_types=1);

use App\Enums\ReceiptTypeEnum;
use App\Filament\Resources\ReceiptResource;
use App\Models\Receipt;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the receipt resource.', function () {
    get(ReceiptResource::getUrl())->assertOk();
});

it('can list receipts in the table.', function () {
    $receipts = Receipt::factory()->count(5)->create();

    livewire(ReceiptResource\Pages\ListReceipts::class)
        ->assertCanSeeTableRecords($receipts);
});

it('can render edit receipt page.', function () {
    $receipt = Receipt::factory()->create();

    get(ReceiptResource::getUrl('edit', [
        'record' => $receipt,
    ]))->assertOk();
});

it('can create receipt model.', function () {
    $user = User::factory()->create();

    livewire(ReceiptResource\Pages\CreateReceipt::class)
        ->fillForm([
            'user_id' => $user->id,
            'type' => ReceiptTypeEnum::PREPAYMENT->value,
            'amount' => 1_500_000,
            'tracking_code' => 'TRK123',
            'is_paya' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Receipt::class, [
        'user_id' => $user->id,
        'type' => ReceiptTypeEnum::PREPAYMENT->value,
        'amount' => 1_500_000,
        'tracking_code' => 'TRK123',
    ]);
});

it('can delete receipt model.', function () {
    $receipt = Receipt::factory()->create();

    livewire(ReceiptResource\Pages\EditReceipt::class, [
        'record' => $receipt->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($receipt);
});
