<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Actions\Catalog\BuildProductCard;
use App\Actions\Catalog\GroupAttributeIds;
use App\Actions\Category\CollectCategoryIds;
use App\Enums\VarietyStatusEnum;
use App\Models\Product;
use App\Models\Tag;
use App\Support\ProductCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class GetTagRows
{
    /**
     * How many items each tag carousel shows.
     */
    private const ROW_LIMIT = 12;

    /**
     * How many tag carousels the home page shows at once.
     *
     * Each row costs its own set of queries, so this is what bounds the cost
     * of the home page: without it the query count grew with however many tags
     * staff happened to feature. Featuring more tags than this is not an error
     * — the extras simply wait their turn behind `home_order`.
     */
    private const MAX_ROWS = 6;

    public function __construct(
        private BuildProductCard $buildProductCard,
        private CollectCategoryIds $collectCategoryIds,
        private GroupAttributeIds $groupAttributeIds,
    ) {}

    /**
     * One product carousel per featured tag, in `home_order`.
     *
     * A tag scopes products by a category (plus its descendants) and/or a set
     * of attributes — the same rules the tag page itself applies, so a row on
     * the home page and the tag's own page always agree about what belongs to
     * it. Tags with no matching products are dropped rather than rendered as
     * an empty carousel — note that happens after the MAX_ROWS cut, so a tag
     * with nothing to show still uses a slot instead of letting the next tag
     * through. Capping the tags we load is what keeps the query count bounded,
     * which is the point of the limit.
     *
     * Cached (`CACHE.md` key 13) — by far the most expensive thing on the home
     * page, since every row costs its own category walk, attribute grouping and
     * product query. Like `GetProductRows`, the signature is only the locale,
     * because nothing here varies per visitor.
     *
     * Invalidation arrives from three directions, and only the first needed new
     * code: `TagObserver` (which tags are featured, in what order, and what they
     * match), `CategoryObserver`/`AttributeObserver` (a tag's scope is resolved
     * through both), and every product/variety/image write through the shared
     * list generation.
     *
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return ProductCache::rememberList(
            'home.tags',
            ['locale' => app()->getLocale()],
            fn (): array => $this->fetch(),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetch(): array
    {
        $tags = Tag::query()
            ->where('show_on_home', true)
            ->with(['category', 'attributes'])
            ->orderBy('home_order')
            ->orderBy('id')
            ->limit(self::MAX_ROWS)
            ->get();

        $rows = $tags
            ->map(fn (Tag $tag): array => [
                'title' => $tag->name,
                'viewAllUrl' => '/tags/'.$tag->slug,
                'products' => $this->products($tag),
            ])
            ->all();

        return array_values(array_filter($rows, fn (array $row): bool => $row['products'] !== []));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function products(Tag $tag): array
    {
        // No category = no category constraint: an attribute-only tag spans
        // every category, exactly as it does on the tag page.
        $categoryIds = $tag->category === null
            ? []
            : ($this->collectCategoryIds)($tag->category);

        $query = Product::query()
            ->published()
            ->when($categoryIds !== [], fn (Builder $q): Builder => $q->whereIn('category_id', $categoryIds))
            ->with([
                'featuredImage',
                'varieties' => fn (Relation $relation) => $relation
                    ->where('status', VarietyStatusEnum::PUBLISHED->value)
                    ->with('image'),
            ]);

        // OR within an attribute group, AND across groups.
        foreach (($this->groupAttributeIds)($tag->attributes->pluck('id')->all()) as $attributeIds) {
            $query->whereHas('attributes', fn (Builder $attribute) => $attribute->whereIn('attributes.id', $attributeIds));
        }

        return $query
            ->latest('id')
            ->limit(self::ROW_LIMIT)
            ->get()
            ->map(fn (Product $product): array => ($this->buildProductCard)($product))
            ->all();
    }
}
