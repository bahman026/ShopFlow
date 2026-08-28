<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Exceptions\InsufficientInventoryException;
use App\Filament\Resources\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Saves inside a transaction so the stock adjustment OrderObserver makes
     * and the status change itself either both land or neither does.
     *
     * Without the transaction a status change that cannot be covered by stock
     * would still be written, and the observer's refusal would surface as a
     * 500 over an order that had already moved.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return DB::transaction(fn (): Model => parent::handleRecordUpdate($record, $data));
        } catch (InsufficientInventoryException $exception) {
            Notification::make()
                ->title(trans('order.inventory_insufficient_title'))
                ->body($exception->forHumans())
                ->danger()
                ->persistent()
                ->send();

            // Halt keeps the user on the form with the record untouched.
            throw new Halt;
        }
    }
}
