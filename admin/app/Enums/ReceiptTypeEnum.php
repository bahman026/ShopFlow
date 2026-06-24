<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum ReceiptTypeEnum: int
{
    use HasOptions;

    case RECEIPT = 1;
    case PREPAYMENT = 2;
    case SHIPPING_REQUEST = 3;

    public function label(): string
    {
        return match ($this) {
            self::RECEIPT => trans('receipt.type_receipt'),
            self::PREPAYMENT => trans('receipt.type_prepayment'),
            self::SHIPPING_REQUEST => trans('receipt.type_shipping_request'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RECEIPT => 'success',
            self::PREPAYMENT => 'info',
            self::SHIPPING_REQUEST => 'warning',
        };
    }
}
