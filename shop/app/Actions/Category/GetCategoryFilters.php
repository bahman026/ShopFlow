<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Enums\ProductStatusEnum;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class GetCategoryFilters
{
    /**
     * Available facets for the category: brands present in it, the attribute
     * groups marked as filters (matched through the product_attribute pivot),
     * and the product price bounds.
     *
     * @param  array<int, int>  $categoryIds
     * @param  array{brands: array<int, string>, attributes: array<int, int>, minPrice: int|null, maxPrice: int|null, inStock: bool, sort: string}  $filters
     * @return array{brands: array<int, array<string, mixed>>, attributeGroups: array<int, array<string, mixed>>, price: array{min: int, max: int}}
     */
    public function __invoke(array $categoryIds, array $filters): array
    {
        return [
            'brands' => $this->brands($categoryIds, $filters['brands']),
            'attributeGroups' => $this->attributeGroups($categoryIds, $filters['attributes']),
            'price' => $this->priceBounds($categoryIds),
        ];
    }

    /**
     * @param  array<int, int>  $categoryIds
     * @param  array<int, string>  $selected
     * @return array<int, array<string, mixed>>
     */
    private function brands(array $categoryIds, array $selected): array
    {
        $counts = Product::query()
            ->published()
            ->whereIn('category_id', $categoryIds)
            ->whereNotNull('brand_id')
            ->selectRaw('brand_id, count(*) as aggregate')
            ->groupBy('brand_id')
            ->pluck('aggregate', 'brand_id');

        return Brand::query()
            ->active()
            ->whereIn('id', $counts->keys()->all())
            ->orderBy('heading')
            ->get()
            ->map(fn (Brand $brand): array => [
                'id' => $brand->id,
                'heading' => $brand->heading,
                'slug' => $brand->slug,
                'count' => (int) ($counts[$brand->id] ?? 0),
                'selected' => in_array($brand->slug, $selected, true),
            ])
            ->all();
    }

    /**
     * Attribute groups flagged as filters for any of the categories, limited to
     * the attribute values actually attached to products in those categories.
     *
     * @param  array<int, int>  $categoryIds
     * @param  array<int, int>  $selected
     * @return array<int, array<string, mixed>>
     */
    private function attributeGroups(array $categoryIds, array $selected): array
    {
        $groupIds = array_map('intval', DB::table('attribute_group_category')
            ->whereIn('category_id', $categoryIds)
            ->where('as_filter', true)
            ->pluck('attribute_group_id')
            ->unique()
            ->all());

        if ($groupIds === []) {
            return [];
        }

        $counts = DB::table('product_attribute')
            ->join('products', 'products.id', '=', 'product_attribute.product_id')
            ->where('products.status', ProductStatusEnum::PUBLISHED->value)
            ->whereIn('products.category_id', $categoryIds)
            ->groupBy('product_attribute.attribute_id')
            ->selectRaw('product_attribute.attribute_id as attribute_id, count(distinct products.id) as aggregate')
            ->pluck('aggregate', 'attribute_id');

        $usedAttributeIds = array_map('intval', $counts->keys()->all());

        if ($usedAttributeIds === []) {
            return [];
        }

        return AttributeGroup::query()
            ->whereIn('id', $groupIds)
            ->with(['attributes' => fn (Relation $relation) => $relation->whereIn('id', $usedAttributeIds)->orderBy('value')])
            ->orderBy('name')
            ->get()
            ->reject(fn (AttributeGroup $group): bool => $group->attributes->isEmpty())
            ->map(fn (AttributeGroup $group): array => [
                'id' => $group->id,
                'name' => $group->name,
                'attributes' => $group->attributes
                    ->map(fn (Attribute $attribute): array => [
                        'id' => $attribute->id,
                        'value' => $attribute->value,
                        'color' => $attribute->color,
                        'count' => (int) ($counts[$attribute->id] ?? 0),
                        'selected' => in_array($attribute->id, $selected, true),
                    ])
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int>  $categoryIds
     * @return array{min: int, max: int}
     */
    private function priceBounds(array $categoryIds): array
    {
        $base = Product::query()
            ->published()
            ->whereIn('category_id', $categoryIds);

        return [
            'min' => (int) ((clone $base)->min('price') ?? 0),
            'max' => (int) ((clone $base)->max('price') ?? 0),
        ];
    }
}
