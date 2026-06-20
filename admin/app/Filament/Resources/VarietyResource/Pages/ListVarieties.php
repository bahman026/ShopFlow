<?php

declare(strict_types=1);

namespace App\Filament\Resources\VarietyResource\Pages;

use App\Filament\Resources\VarietyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVarieties extends ListRecords
{
    protected static string $resource = VarietyResource::class;

    protected ?string $subheading = 'Varieties are the purchasable options of a product (e.g. a T-shirt in Red or Blue, each with its own price and inventory). Each variety belongs to one product and is linked to one attribute from the product\'s attribute group. Selecting an attribute auto-fills the variety\'s label and color.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
