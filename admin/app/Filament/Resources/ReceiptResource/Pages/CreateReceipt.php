<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReceipt extends CreateRecord
{
    protected static string $resource = ReceiptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
