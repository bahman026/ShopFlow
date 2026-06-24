<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserConfigResource\Pages;

use App\Filament\Resources\UserConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserConfigs extends ListRecords
{
    protected static string $resource = UserConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
