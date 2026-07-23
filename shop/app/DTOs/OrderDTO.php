<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class OrderDTO
{
    /**
     * @param  array<int, OrderLineDTO>  $lines
     */
    public function __construct(
        public int $id,
        public string $status,
        public string $statusLabel,
        public string $createdAt,
        public int $totalProductsPrice,
        public int $discount,
        public int $shippingCost,
        public int $taxPrice,
        public int $totalPrice,
        public array $lines,
        public ?AddressDTO $address,
        public ?string $shippingMethodName,
        public ?string $shippingLineName,
        public ?string $refId,
        public ?string $paidAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'statusLabel' => $this->statusLabel,
            'createdAt' => $this->createdAt,
            'totalProductsPrice' => $this->totalProductsPrice,
            'discount' => $this->discount,
            'shippingCost' => $this->shippingCost,
            'taxPrice' => $this->taxPrice,
            'totalPrice' => $this->totalPrice,
            'lines' => array_map(fn (OrderLineDTO $line): array => $line->toArray(), $this->lines),
            'address' => $this->address?->toArray(),
            'shippingMethodName' => $this->shippingMethodName,
            'shippingLineName' => $this->shippingLineName,
            'refId' => $this->refId,
            'paidAt' => $this->paidAt,
        ];
    }
}
