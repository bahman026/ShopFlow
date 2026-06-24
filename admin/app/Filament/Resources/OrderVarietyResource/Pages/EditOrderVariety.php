<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderVarietyResource\Pages;

use App\Filament\Resources\OrderVarietyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderVariety extends EditRecord
{
    protected static string $resource = OrderVarietyResource::class;

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
