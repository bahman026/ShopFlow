<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class AddressDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $phone,
        public ?string $postalCode,
        public string $address,
        public ?string $plate,
        public ?string $unit,
        public ?string $note,
        public ?string $latitude,
        public ?string $longitude,
        public int $cityId,
        public ?string $cityName,
        public ?int $provinceId,
        public ?string $provinceName,
        public bool $prime,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'postalCode' => $this->postalCode,
            'address' => $this->address,
            'plate' => $this->plate,
            'unit' => $this->unit,
            'note' => $this->note,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'cityId' => $this->cityId,
            'cityName' => $this->cityName,
            'provinceId' => $this->provinceId,
            'provinceName' => $this->provinceName,
            'prime' => $this->prime,
        ];
    }
}
