<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;

    protected ?string $subheading = null;

    public function mount(): void
    {
        parent::mount();
        $this->subheading = trans('receipt.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
