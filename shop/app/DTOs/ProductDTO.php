<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ProductDTO
{
    /**
     * @param  array<int, ImageDTO>  $gallery
     * @param  array{heading: string, url: string}|null  $brand
     * @param  array{heading: string, url: string}|null  $category
     * @param  array<int, array<string, mixed>>  $variantAxes
     * @param  array<int, VarietyDTO>  $varieties
     * @param  array<int, array{value: string}>  $highlights
     * @param  array<int, array{value: string}>  $specs
     * @param  array<int, ReviewDTO>  $reviews
     */
    public function __construct(
        public int $id,
        public string $heading,
        public string $url,
        public ?string $content,
        public ?string $title,
        public ?string $description,
        public bool $noIndex,
        public ?string $canonical,
        public ?ImageDTO $image,
        public array $gallery,
        public ?array $brand,
        public ?array $category,
        public int $price,
        public ?int $salePrice,
        public ?int $discountPercent,
        public bool $inStock,
        public array $variantAxes,
        public array $varieties,
        public array $highlights,
        public array $specs,
        public array $reviews,
        public int $reviewCount,
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
            'content' => $this->content,
            'title' => $this->title,
            'description' => $this->description,
            'noIndex' => $this->noIndex,
            'canonical' => $this->canonical,
            'image' => $this->image?->toArray(),
            'gallery' => array_map(fn (ImageDTO $image): array => $image->toArray(), $this->gallery),
            'brand' => $this->brand,
            'category' => $this->category,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'discountPercent' => $this->discountPercent,
            'inStock' => $this->inStock,
            'variantAxes' => $this->variantAxes,
            'varieties' => array_map(fn (VarietyDTO $variety): array => $variety->toArray(), $this->varieties),
            'highlights' => $this->highlights,
            'specs' => $this->specs,
            'reviews' => array_map(fn (ReviewDTO $review): array => $review->toArray(), $this->reviews),
            'reviewCount' => $this->reviewCount,
        ];
    }
}
