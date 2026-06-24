<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderNoteResource\Pages;

use App\Filament\Resources\OrderNoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderNotes extends ListRecords
{
    protected static string $resource = OrderNoteResource::class;

    protected ?string $subheading = null;

    public function mount(): void
    {
        parent::mount();
        $this->subheading = trans('order_note.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
