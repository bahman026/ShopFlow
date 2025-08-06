<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeGroupCategoryResource\Pages;

use App\Filament\Resources\AttributeGroupCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttributeGroupCategory extends CreateRecord
{
    protected static string $resource = AttributeGroupCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
