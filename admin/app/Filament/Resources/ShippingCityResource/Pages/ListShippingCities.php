<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingCityResource\Pages;

use App\Filament\Resources\ShippingCityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShippingCities extends ListRecords
{
    protected static string $resource = ShippingCityResource::class;

    protected ?string $subheading = 'Per-city (or per-province) shipping availability, cost overrides, and delivery schedules for each shipping method.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
