<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingCityResource\Pages;

use App\Filament\Resources\ShippingCityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShippingCity extends EditRecord
{
    protected static string $resource = ShippingCityResource::class;

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
