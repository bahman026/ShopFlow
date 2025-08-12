<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeGroupCategoryResource\Pages;

use App\Filament\Resources\AttributeGroupCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttributeGroupCategory extends EditRecord
{
    protected static string $resource = AttributeGroupCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //            Actions\DeleteAction::make(),
        ];

    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
