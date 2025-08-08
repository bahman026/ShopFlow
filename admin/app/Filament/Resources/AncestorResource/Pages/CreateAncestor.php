<?php

declare(strict_types=1);

namespace App\Filament\Resources\AncestorResource\Pages;

use App\Filament\Resources\AncestorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAncestor extends CreateRecord
{
    protected static string $resource = AncestorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
