<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Actions\Catalog\BuildProductCard;
use App\Enums\VarietyStatusEnum;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class GetProductRows
{
    /**
     * How many items each product carousel shows.
     */
    private const ROW_LIMIT = 12;

    public function __construct(private BuildProductCard $buildProductCard) {}

    /**
     * Two product carousels: newest and most viewed. Empty rows are dropped.
     *
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        $rows = [
            ['title' => 'جدیدترین محصولات', 'viewAllUrl' => '/products?sort=newest', 'query' => fn (Builder $q) => $q->latest('id')],
            ['title' => 'پربازدیدترین محصولات', 'viewAllUrl' => '/products?sort=popular', 'query' => fn (Builder $q) => $q->orderByDesc('seen')],
        ];

        return array_values(array_filter(array_map(function (array $row): array {
            $products = Product::query()
                ->published()
                ->with(['featuredImage', 'varieties' => fn (Relation $q) => $q->where('status', VarietyStatusEnum::PUBLISHED->value)->with('image')])
                ->tap($row['query'])
                ->limit(self::ROW_LIMIT)
                ->get()
                ->map(fn (Product $product): array => ($this->buildProductCard)($product))
                ->all();

            return [
                'title' => $row['title'],
                'viewAllUrl' => $row['viewAllUrl'],
                'products' => $products,
            ];
        }, $rows), fn (array $row): bool => $row['products'] !== []));
    }
}
