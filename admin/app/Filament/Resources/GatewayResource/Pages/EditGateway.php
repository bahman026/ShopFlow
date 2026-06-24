<?php

declare(strict_types=1);

namespace App\Filament\Resources\GatewayResource\Pages;

use App\Filament\Resources\GatewayResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGateway extends EditRecord
{
    protected static string $resource = GatewayResource::class;

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
