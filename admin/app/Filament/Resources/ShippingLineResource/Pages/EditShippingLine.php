<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingLineResource\Pages;

use App\Filament\Resources\ShippingLineResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShippingLine extends EditRecord
{
    protected static string $resource = ShippingLineResource::class;

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
