<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function beforeCreate(): void
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['attributes']);

        return $data;
    }
}
