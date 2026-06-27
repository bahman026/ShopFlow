<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $displayName,
        public string $mobile,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'displayName' => $this->displayName,
            'mobile' => $this->mobile,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
        ];
    }
}
