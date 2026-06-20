<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected ?string $subheading = 'Products are the items sold on ShopFlow. Each product belongs to a category and brand, has a base price, and can have multiple varieties (e.g. different colors or sizes) each with their own price and inventory. Set an attribute group to define which attribute type differentiates the varieties. Product-level attributes describe shared specifications shown on the product page.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
