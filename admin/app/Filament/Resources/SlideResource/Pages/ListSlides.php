<?php

declare(strict_types=1);

namespace App\Filament\Resources\SlideResource\Pages;

use App\Filament\Resources\SlideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSlides extends ListRecords
{
    protected static string $resource = SlideResource::class;

    protected ?string $subheading = 'Slides are the individual items inside a slider. Each slide belongs to one slider and has an image, an optional heading, label, and a URL to link to when clicked.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
