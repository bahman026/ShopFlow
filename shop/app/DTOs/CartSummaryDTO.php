<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CartSummaryDTO
{
    public function __construct(
        public int $count,
        public int $itemsTotal,
        public int $discount,
        public int $payable,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'count' => $this->count,
            'itemsTotal' => $this->itemsTotal,
            'discount' => $this->discount,
            'payable' => $this->payable,
        ];
    }
}
