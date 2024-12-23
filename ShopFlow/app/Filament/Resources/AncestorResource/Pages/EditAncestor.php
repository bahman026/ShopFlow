<?php

declare(strict_types=1);

namespace App\Filament\Resources\AncestorResource\Pages;

use App\Filament\Resources\AncestorResource;
use Filament\Resources\Pages\EditRecord;

class EditAncestor extends EditRecord
{
    protected static string $resource = AncestorResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];

    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
