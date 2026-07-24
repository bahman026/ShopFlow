<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Category;
use App\Models\Product;

class BuildProductBreadcrumbs
{
    /**
     * Breadcrumb trail: home, category ancestors, then the product itself.
     *
     * @return array<int, array{heading: string, url: string|null}>
     */
    public function __invoke(Product $product): array
    {
        $chain = [];
        $category = $product->category;

        while ($category instanceof Category) {
            array_unshift($chain, [
                'heading' => $category->heading,
                'url' => '/categories/'.$category->slug,
            ]);
            $category = $category->parent;
        }

        return [
            ['heading' => 'خانه', 'url' => '/'],
            ...$chain,
            ['heading' => $product->heading, 'url' => null],
        ];
    }
}
