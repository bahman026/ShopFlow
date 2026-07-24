<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class OrderLineDTO
{
    public function __construct(
        public string $heading,
        public ?string $url,
        public ?ImageDTO $image,
        public ?string $color,
        public int $quantity,
        public int $unitPrice,
        public int $finalPrice,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'heading' => $this->heading,
            'url' => $this->url,
            'image' => $this->image?->toArray(),
            'color' => $this->color,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'finalPrice' => $this->finalPrice,
        ];
    }
}
