<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Models\Category;

class BuildCategoryBreadcrumbs
{
    /**
     * Breadcrumb trail: home, parent categories (root first), then the current
     * category as the active (link-less) crumb.
     *
     * @return array<int, array{heading: string, url: string|null}>
     */
    public function __invoke(Category $category): array
    {
        $chain = [];
        $parent = $category->parent;

        while ($parent instanceof Category) {
            array_unshift($chain, [
                'heading' => $parent->heading,
                'url' => '/categories/'.$parent->slug,
            ]);
            $parent = $parent->parent;
        }

        return [
            ['heading' => 'خانه', 'url' => '/'],
            ...$chain,
            ['heading' => $category->heading, 'url' => null],
        ];
    }
}
