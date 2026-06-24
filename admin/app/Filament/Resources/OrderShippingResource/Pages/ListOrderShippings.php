<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippingResource\Pages;

use App\Filament\Resources\OrderShippingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderShippings extends ListRecords
{
    protected static string $resource = OrderShippingResource::class;

    protected ?string $subheading = null;

    public function mount(): void
    {
        parent::mount();
        $this->subheading = trans('order_shipping.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
