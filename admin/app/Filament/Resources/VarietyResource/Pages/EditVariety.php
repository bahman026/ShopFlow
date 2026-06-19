<?php

declare(strict_types=1);

namespace App\Filament\Resources\VarietyResource\Pages;

use App\Filament\Resources\VarietyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVariety extends EditRecord
{
    protected static string $resource = VarietyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
