<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class VarietyDTO
{
    /**
     * @param  array<int, array<int, string>>  $options  attribute-group id => values
     */
    public function __construct(
        public int $id,
        public ?string $label,
        public ?string $color,
        public int $price,
        public ?int $salePrice,
        public ?int $discountPercent,
        public bool $inStock,
        public int $inventory,
        public ?ImageDTO $image,
        public array $options,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'color' => $this->color,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'discountPercent' => $this->discountPercent,
            'inStock' => $this->inStock,
            'inventory' => $this->inventory,
            'image' => $this->image?->toArray(),
            'options' => $this->options,
        ];
    }
}
