<?php

declare(strict_types=1);

namespace App\Filament\Resources\AncestorResource\Pages;

use App\Filament\Resources\AncestorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAncestors extends ListRecords
{
    protected static string $resource = AncestorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
