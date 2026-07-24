<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Actions\Catalog\TransformImage;
use App\DTOs\CategoryDTO;
use App\Models\Category;

class BuildCategoryDetail
{
    public function __construct(private TransformImage $transformImage) {}

    public function __invoke(Category $category): CategoryDTO
    {
        return new CategoryDTO(
            id: $category->id,
            heading: $category->heading,
            url: '/categories/'.$category->slug,
            title: $category->title,
            description: $category->description,
            content: $category->content,
            noIndex: (bool) $category->no_index,
            canonical: $category->canonical,
            image: ($this->transformImage)($category->image),
        );
    }
}
