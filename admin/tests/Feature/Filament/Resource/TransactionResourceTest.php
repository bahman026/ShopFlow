<?php

declare(strict_types=1);

use App\Enums\TransactionPortEnum;
use App\Enums\TransactionStatusEnum;
use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    login();
});

it('can render index page of the transaction resource.', function () {
    get(TransactionResource::getUrl())->assertOk();
});

it('can list transactions in the table.', function () {
    $transactions = Transaction::factory()->count(5)->create();

    livewire(TransactionResource\Pages\ListTransactions::class)
        ->assertCanSeeTableRecords($transactions);
});

it('can render edit transaction page.', function () {
    $transaction = Transaction::factory()->create();

    get(TransactionResource::getUrl('edit', [
        'record' => $transaction,
    ]))->assertOk();
});

it('can create transaction model.', function () {
    $user = User::factory()->create();

    livewire(TransactionResource\Pages\CreateTransaction::class)
        ->fillForm([
            'user_id' => $user->id,
            'port' => TransactionPortEnum::MELLAT->value,
            'status' => TransactionStatusEnum::SUCCESS->value,
            'amount' => 2_000_000,
            'ref_id' => 'REF999',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Transaction::class, [
        'user_id' => $user->id,
        'port' => TransactionPortEnum::MELLAT->value,
        'status' => TransactionStatusEnum::SUCCESS->value,
        'amount' => 2_000_000,
        'ref_id' => 'REF999',
    ]);
});

it('can delete transaction model.', function () {
    $transaction = Transaction::factory()->create();

    livewire(TransactionResource\Pages\EditTransaction::class, [
        'record' => $transaction->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($transaction);
});
