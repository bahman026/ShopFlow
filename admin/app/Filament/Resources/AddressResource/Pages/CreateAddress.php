<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAddress extends CreateRecord
{
    protected static string $resource = AddressResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
