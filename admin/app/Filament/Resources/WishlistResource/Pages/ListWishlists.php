<?php

declare(strict_types=1);

namespace App\Filament\Resources\WishlistResource\Pages;

use App\Filament\Resources\WishlistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWishlists extends ListRecords
{
    protected static string $resource = WishlistResource::class;

    protected ?string $subheading = null;

    public function mount(): void
    {
        parent::mount();
        $this->subheading = trans('wishlist.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
