<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserConfigResource\Pages;

use App\Filament\Resources\UserConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserConfig extends CreateRecord
{
    protected static string $resource = UserConfigResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
