<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuItems extends ListRecords
{
    protected static string $resource = MenuItemResource::class;

    protected ?string $subheading = null;

    public function mount(): void
    {
        parent::mount();
        $this->subheading = trans('menu_item.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
