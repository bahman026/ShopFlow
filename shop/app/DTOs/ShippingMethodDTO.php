<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ShippingMethodDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $lineName,
        public ?string $description,
        public ?string $sendingDays,
        public ?int $cost,
        public bool $payOnDelivery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lineName' => $this->lineName,
            'description' => $this->description,
            'sendingDays' => $this->sendingDays,
            'cost' => $this->cost,
            'payOnDelivery' => $this->payOnDelivery,
        ];
    }
}
