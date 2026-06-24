<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingLineResource\Pages;

use App\Filament\Resources\ShippingLineResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShippingLines extends ListRecords
{
    protected static string $resource = ShippingLineResource::class;

    protected ?string $subheading = null;

    public function mount(): void
    {
        parent::mount();
        $this->subheading = trans('shipping_line.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
