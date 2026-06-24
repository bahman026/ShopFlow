<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderVarietyResource\Pages;

use App\Filament\Resources\OrderVarietyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderVarieties extends ListRecords
{
    protected static string $resource = OrderVarietyResource::class;

    protected ?string $subheading = null;

    public function mount(): void
    {
        parent::mount();
        $this->subheading = trans('order_variety.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
