<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Models\Category;

class CollectCategoryIds
{
    /**
     * The category's own id plus every descendant id, so a parent category page
     * also lists products that live in its sub-categories.
     *
     * @return array<int, int>
     */
    public function __invoke(Category $category): array
    {
        $ids = [$category->id];
        $frontier = [$category->id];

        while (true) {
            $children = Category::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            $children = array_values(array_diff(array_map('intval', $children), $ids));

            if ($children === []) {
                break;
            }

            $ids = array_merge($ids, $children);
            $frontier = $children;
        }

        return $ids;
    }
}
