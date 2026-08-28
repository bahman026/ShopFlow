<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CouponDTO
{
    public function __construct(
        // Not exposed in toArray(): the storefront only needs it to write
        // orders.coupon_id and to count the use once payment succeeds.
        public int $id,
        public string $code,
        public string $name,
        public int $discount,
        public bool $freeShipping,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'discount' => $this->discount,
            'freeShipping' => $this->freeShipping,
        ];
    }
}
