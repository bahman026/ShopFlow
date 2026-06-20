<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function beforeSave(): void
    {
        $categoryId = (int) ($this->data['category_id'] ?? 0);

        if ($categoryId === 0) {
            return;
        }

        $missing = ProductResource::missingRequiredGroups(
            $this->data['attributes'] ?? [],
            $categoryId,
        );

        if ($missing->isNotEmpty()) {
            Notification::make()
                ->danger()
                ->title('Required attribute groups missing')
                ->body('Add at least one attribute from: ' . $missing->join(', '))
                ->send();

            $this->halt();
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['attributes']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
