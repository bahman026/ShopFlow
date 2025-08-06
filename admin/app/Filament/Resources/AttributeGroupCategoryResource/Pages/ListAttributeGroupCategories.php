<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeGroupCategoryResource\Pages;

use App\Filament\Resources\AttributeGroupCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttributeGroupCategories extends ListRecords
{
    protected static string $resource = AttributeGroupCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
