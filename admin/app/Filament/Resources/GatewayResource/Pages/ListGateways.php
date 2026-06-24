<?php

declare(strict_types=1);

namespace App\Filament\Resources\GatewayResource\Pages;

use App\Filament\Resources\GatewayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGateways extends ListRecords
{
    protected static string $resource = GatewayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
