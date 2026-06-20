<?php

declare(strict_types=1);

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected ?string $subheading = 'Coupons are manual discount codes entered by the customer at checkout. Unlike auto-applied discounts, a coupon requires a code. You can limit a coupon by minimum cart price, maximum discount cap, total usage, audience, and time window. Optionally scope it to specific products, varieties, or categories.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
