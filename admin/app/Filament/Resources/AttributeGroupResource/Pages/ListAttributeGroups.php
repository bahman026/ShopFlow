<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeGroupResource\Pages;

use App\Filament\Resources\AttributeGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttributeGroups extends ListRecords
{
    protected static string $resource = AttributeGroupResource::class;

    protected ?string $subheading = 'An attribute group is a set of attributes that share a common property (e.g. "Color" contains Red, Blue, Green). Each group belongs to an ancestor and is linked to categories via Attribute Group Categories. Products then use a group to define their variety differentiator.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
