<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuItems extends ListRecords
{
    protected static string $resource = MenuItemResource::class;

    protected ?string $subheading = 'Menu items are the individual links inside a menu. Each item belongs to one menu and can optionally be nested under a top-level item as a child. Use the Order field to control their display sequence.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
