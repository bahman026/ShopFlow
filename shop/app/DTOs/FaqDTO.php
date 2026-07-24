<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class FaqDTO
{
    public function __construct(
        public int $id,
        public string $heading,
        public string $content,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'heading' => $this->heading,
            'content' => $this->content,
        ];
    }
}
