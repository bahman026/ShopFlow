<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CouponDTO
{
    public function __construct(
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
