<?php

declare(strict_types=1);

namespace App\Actions\Tag;

use App\Models\Category;
use App\Models\Tag;

class BuildTagBreadcrumbs
{
    /**
     * Breadcrumb trail: home, the tag's category chain (root first, all
     * linked) when the tag has a category, then the tag as the active
     * (link-less) crumb. Home → [Category …] → Tag.
     *
     * @return array<int, array{heading: string, url: string|null}>
     */
    public function __invoke(Tag $tag): array
    {
        $chain = [];
        $category = $tag->category;

        while ($category instanceof Category) {
            array_unshift($chain, [
                'heading' => $category->heading,
                'url' => '/categories/'.$category->slug,
            ]);
            $category = $category->parent;
        }

        return [
            ['heading' => trans('messages.breadcrumb.home'), 'url' => '/'],
            ...$chain,
            ['heading' => $tag->name, 'url' => null],
        ];
    }
}
