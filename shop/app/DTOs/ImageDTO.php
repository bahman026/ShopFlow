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
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['url'],
            $data['alt'] ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
