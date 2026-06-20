<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingCityResource\Pages;

use App\Filament\Resources\ShippingCityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingCity extends CreateRecord
{
    protected static string $resource = ShippingCityResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
