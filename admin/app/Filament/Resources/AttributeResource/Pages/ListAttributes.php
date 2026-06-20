<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\Pages;

use App\Filament\Resources\AttributeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttributes extends ListRecords
{
    protected static string $resource = AttributeResource::class;

    protected ?string $subheading = 'Attributes are the individual values inside an attribute group (e.g. "Red", "Blue", "Green" inside "Color"). Each attribute can have an optional color swatch. When linked to a variety via attribute_id, the variety\'s value and color are auto-filled from the attribute.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
