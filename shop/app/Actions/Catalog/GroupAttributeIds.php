<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Models\Attribute;
use Illuminate\Support\Collection;

class GroupAttributeIds
{
    /**
     * Group selected attribute ids by their attribute group, so each group
     * becomes one AND-ed constraint while values inside it stay OR-ed.
     *
     * Shared by every place that narrows products by attribute — the category
     * listing, the tag page, and the featured-tag rows on the home page — so
     * the facet rules cannot drift between them.
     *
     * @param  array<int, int>  $attributeIds
     * @return array<int, array<int, int>>
     */
    public function __invoke(array $attributeIds): array
    {
        if ($attributeIds === []) {
            return [];
        }

        return Attribute::query()
            ->whereIn('id', $attributeIds)
            ->get()
            ->groupBy('attribute_group_id')
            ->map(fn (Collection $group): array => $group->pluck('id')->all())
            ->values()
            ->all();
    }
}
