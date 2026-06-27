<?php

declare(strict_types=1);

namespace App\Contracts;

interface ProductSearch
{
    /**
     * Keyword search over products, returning paginated product cards.
     *
     * Swap the bound implementation (e.g. Elasticsearch) without touching
     * callers; the return shape must stay stable.
     *
     * @param  array{sort: string}  $options
     * @return array{data: array<int, array<string, mixed>>, meta: array{currentPage: int, lastPage: int, perPage: int, total: int, from: int|null, to: int|null}}
     */
    public function search(string $term, array $options): array;

    /**
     * Lightweight product matches for the header search autocomplete.
     *
     * @return array<int, array{heading: string, url: string, categoryHeading: string|null}>
     */
    public function suggest(string $term, int $limit = 8): array;
}
