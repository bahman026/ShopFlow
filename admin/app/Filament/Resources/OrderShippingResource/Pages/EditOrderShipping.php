<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippingResource\Pages;

use App\Filament\Resources\OrderShippingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderShipping extends EditRecord
{
    protected static string $resource = OrderShippingResource::class;

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
