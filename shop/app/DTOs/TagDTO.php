<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class TagDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $url,
        public ?string $title,
        public ?string $description,
        public ?string $content,
        public bool $noIndex,
        public ?string $canonical,
        public ?string $categoryName,
        public ?string $categoryUrl,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'noIndex' => $this->noIndex,
            'canonical' => $this->canonical,
            'categoryName' => $this->categoryName,
            'categoryUrl' => $this->categoryUrl,
        ];
    }
}
