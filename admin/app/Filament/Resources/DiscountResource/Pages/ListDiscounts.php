<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscounts extends ListRecords
{
    protected static string $resource = DiscountResource::class;

    protected ?string $subheading = 'Discounts are automatic price rules applied to a specific variety when their conditions are met — no code needed. Each discount targets one variety and can be limited by quantity, time window, audience, and usage cap. When an order qualifies, the discount amount is stored on the order for historical reference.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
