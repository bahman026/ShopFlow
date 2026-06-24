<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use App\Models\Address;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAddress extends EditRecord
{
    protected static string $resource = AddressResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Addresses are immutable: orders may reference them, so we never change an
     * existing record. Saving an edit instead creates a new address. The new
     * one inherits the edited address's primary status: if the edited address
     * was primary the new one becomes primary (demoting the old via the model
     * hook); otherwise it is created as a plain, non-primary address.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['prime'] = (bool) $record->getAttribute('prime');

        return Address::create($data);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return trans('address.edit_creates_new');
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
