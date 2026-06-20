<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    protected ?string $subheading = 'Menus group navigation links shown in the header, footer, or any other frontend placement. Create a menu first, then add items to it via the Menu Items section.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
