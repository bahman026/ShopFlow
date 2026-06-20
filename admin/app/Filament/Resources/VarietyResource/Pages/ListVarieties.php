<?php

declare(strict_types=1);

namespace App\Filament\Resources\VarietyResource\Pages;

use App\Filament\Resources\VarietyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVarieties extends ListRecords
{
    protected static string $resource = VarietyResource::class;

    protected ?string $subheading = null;

    public function mount(): void
    {
        parent::mount();
        $this->subheading = trans('variety.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
