<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class BrandDTO
{
    public function __construct(
        public int $id,
        public string $heading,
        public string $url,
        public ?string $title,
        public ?string $description,
        public ?string $content,
        public bool $noIndex,
        public ?string $canonical,
        public ?ImageDTO $image,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'heading' => $this->heading,
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'noIndex' => $this->noIndex,
            'canonical' => $this->canonical,
            'image' => $this->image?->toArray(),
        ];
    }
}
