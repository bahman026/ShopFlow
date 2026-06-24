<?php

declare(strict_types=1);

namespace App\Filament\Resources\GatewayResource\Pages;

use App\Filament\Resources\GatewayResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGateway extends CreateRecord
{
    protected static string $resource = GatewayResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
