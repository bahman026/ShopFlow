<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CartLineDTO
{
    /**
     * @param  array<int, array{group: string|null, value: string}>  $attributes
     */
    public function __construct(
        public int $id,
        public int $varietyId,
        public string $heading,
        public string $url,
        public ?ImageDTO $image,
        public ?string $color,
        public array $attributes,
        public int $unitPrice,
        public int $originalPrice,
        public ?int $discountPercent,
        public int $count,
        public int $inventory,
        public bool $inStock,
    ) {}

    public function lineTotal(): int
    {
        return $this->unitPrice * $this->count;
    }

    public function lineOriginalTotal(): int
    {
        return $this->originalPrice * $this->count;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'varietyId' => $this->varietyId,
            'heading' => $this->heading,
            'url' => $this->url,
            'image' => $this->image?->toArray(),
            'color' => $this->color,
            'attributes' => $this->attributes,
            'unitPrice' => $this->unitPrice,
            'originalPrice' => $this->originalPrice,
            'discountPercent' => $this->discountPercent,
            'count' => $this->count,
            'inventory' => $this->inventory,
            'inStock' => $this->inStock,
            'lineTotal' => $this->lineTotal(),
            'lineOriginalTotal' => $this->lineOriginalTotal(),
        ];
    }
}
