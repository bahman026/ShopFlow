<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserConfigResource\Pages;

use App\Filament\Resources\UserConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserConfig extends EditRecord
{
    protected static string $resource = UserConfigResource::class;

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
