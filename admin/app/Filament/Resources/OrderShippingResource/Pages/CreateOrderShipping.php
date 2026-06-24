<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippingResource\Pages;

use App\Filament\Resources\OrderShippingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderShipping extends CreateRecord
{
    protected static string $resource = OrderShippingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
