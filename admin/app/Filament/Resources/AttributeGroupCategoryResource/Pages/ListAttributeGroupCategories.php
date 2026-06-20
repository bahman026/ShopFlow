<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeGroupCategoryResource\Pages;

use App\Filament\Resources\AttributeGroupCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttributeGroupCategories extends ListRecords
{
    protected static string $resource = AttributeGroupCategoryResource::class;

    protected ?string $subheading = 'Links an attribute group to a category. This tells the system which attributes are relevant for products in that category. Use "As Filter" to show the group as a filter panel on the category page, and "Required" to force admins to pick an attribute from this group when creating a product in that category.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
