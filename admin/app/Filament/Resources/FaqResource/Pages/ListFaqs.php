<?php

declare(strict_types=1);

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFaqs extends ListRecords
{
    protected static string $resource = FaqResource::class;

    protected ?string $subheading = 'Manage frequently asked questions shown on the FAQ page and other positions (e.g. homepage).';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
