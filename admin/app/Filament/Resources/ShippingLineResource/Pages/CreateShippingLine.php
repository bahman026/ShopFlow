<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingLineResource\Pages;

use App\Filament\Resources\ShippingLineResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingLine extends CreateRecord
{
    protected static string $resource = ShippingLineResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
