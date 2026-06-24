<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingMethodResource\Pages;

use App\Filament\Resources\ShippingMethodResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShippingMethod extends EditRecord
{
    protected static string $resource = ShippingMethodResource::class;

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
}
