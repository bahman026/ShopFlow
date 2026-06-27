<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ImageDTO
{
    public function __construct(
        public string $url,
        public string $alt = '',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
