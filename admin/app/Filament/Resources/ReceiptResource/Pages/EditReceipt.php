<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReceipt extends EditRecord
{
    protected static string $resource = ReceiptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
