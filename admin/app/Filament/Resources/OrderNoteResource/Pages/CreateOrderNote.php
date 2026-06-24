<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderNoteResource\Pages;

use App\Filament\Resources\OrderNoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderNote extends CreateRecord
{
    protected static string $resource = OrderNoteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
