<?php

declare(strict_types=1);

namespace App\Search;

use App\Actions\Catalog\BuildProductCard;
use App\Contracts\ProductSearch;
use App\Enums\VarietyStatusEnum;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class DatabaseProductSearch implements ProductSearch
{
    /**
     * Results shown per search page.
     */
    private const PER_PAGE = 24;

    public function __construct(private BuildProductCard $buildProductCard) {}

    /**
     * Match each search word (case-insensitively) against the product heading,
     * title, description or its brand name. Words are AND-ed for relevance;
     * fields within a word are OR-ed.
     *
     * @param  array{sort: string}  $options
     * @return array{data: array<int, array<string, mixed>>, meta: array{currentPage: int, lastPage: int, perPage: int, total: int, from: int|null, to: int|null}}
     */
    public function search(string $term, array $options): array
    {
        $words = preg_split('/\s+/', trim($term), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return $this->empty();
        }

        $query = Product::query()
            ->published()
            ->with([
                'featuredImage',
                'varieties' => fn (Relation $relation) => $relation->where('status', VarietyStatusEnum::PUBLISHED->value)->with('image'),
            ]);

        foreach ($words as $word) {
            $like = '%'.$word.'%';

            $query->where(function (Builder $group) use ($like): void {
                $group->where('heading', 'ilike', $like)
                    ->orWhere('title', 'ilike', $like)
                    ->orWhere('description', 'ilike', $like)
                    ->orWhereHas('brand', fn (Builder $brand) => $brand->where('heading', 'ilike', $like));
            });
        }

        $this->applySort($query, $options['sort']);

        $paginator = $query->paginate(self::PER_PAGE)->withQueryString();

        /** @var array<int, Product> $items */
        $items = $paginator->items();

        return [
            'data' => array_map(fn (Product $product): array => ($this->buildProductCard)($product), $items),
            'meta' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * Top products (by views) whose name or brand matches the term, used for
     * the header search dropdown.
     *
     * @return array<int, array{heading: string, url: string, categoryHeading: string|null}>
     */
    public function suggest(string $term, int $limit = 8): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $like = '%'.$term.'%';

        return Product::query()
            ->published()
            ->with('category:id,heading')
            ->where(function (Builder $group) use ($like): void {
                $group->where('heading', 'ilike', $like)
                    ->orWhereHas('brand', fn (Builder $brand) => $brand->where('heading', 'ilike', $like));
            })
            ->orderByDesc('seen')
            ->limit($limit)
            ->get(['id', 'heading', 'slug', 'category_id'])
            ->map(fn (Product $product): array => [
                'heading' => $product->heading,
                'url' => '/products/'.$product->slug,
                'categoryHeading' => $product->category?->heading,
            ])
            ->all();
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'cheapest' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('seen'),
            default => $query->orderByDesc('id'),
        };
    }

    /**
     * Empty result set for a blank query.
     *
     * @return array{data: array<int, array<string, mixed>>, meta: array{currentPage: int, lastPage: int, perPage: int, total: int, from: int|null, to: int|null}}
     */
    private function empty(): array
    {
        return [
            'data' => [],
            'meta' => [
                'currentPage' => 1,
                'lastPage' => 1,
                'perPage' => self::PER_PAGE,
                'total' => 0,
                'from' => null,
                'to' => null,
            ],
        ];
    }
}
