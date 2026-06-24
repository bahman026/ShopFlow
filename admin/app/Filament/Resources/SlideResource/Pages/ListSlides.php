<?php

declare(strict_types=1);

namespace App\Filament\Resources\SlideResource\Pages;

use App\Filament\Resources\SlideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSlides extends ListRecords
{
    protected static string $resource = SlideResource::class;

    protected ?string $subheading = null;

    public function mount(): void
    {
        parent::mount();
        $this->subheading = trans('slide.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
