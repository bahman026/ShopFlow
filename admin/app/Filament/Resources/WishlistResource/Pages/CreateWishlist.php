<?php

declare(strict_types=1);

namespace App\Filament\Resources\WishlistResource\Pages;

use App\Filament\Resources\WishlistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWishlist extends CreateRecord
{
    protected static string $resource = WishlistResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
