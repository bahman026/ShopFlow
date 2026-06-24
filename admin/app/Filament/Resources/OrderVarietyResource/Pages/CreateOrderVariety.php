<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderVarietyResource\Pages;

use App\Filament\Resources\OrderVarietyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderVariety extends CreateRecord
{
    protected static string $resource = OrderVarietyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
