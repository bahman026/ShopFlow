<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ReviewDTO
{
    public function __construct(
        public int $id,
        public ?string $heading,
        public ?string $content,
        public ?string $author = null,
        public ?string $date = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['heading'] ?? null,
            $data['content'] ?? null,
            $data['author'] ?? null,
            $data['date'] ?? null,
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
