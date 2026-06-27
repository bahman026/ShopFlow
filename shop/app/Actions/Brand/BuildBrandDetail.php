<?php

declare(strict_types=1);

namespace App\Actions\Brand;

use App\Actions\Catalog\TransformImage;
use App\DTOs\BrandDTO;
use App\Models\Brand;

class BuildBrandDetail
{
    public function __construct(private TransformImage $transformImage) {}

    public function __invoke(Brand $brand): BrandDTO
    {
        return new BrandDTO(
            id: $brand->id,
            heading: $brand->heading,
            url: '/brands/'.$brand->slug,
            title: $brand->title,
            description: $brand->description,
            content: $brand->content,
            noIndex: (bool) $brand->no_index,
            canonical: $brand->canonical,
            image: ($this->transformImage)($brand->image),
        );
    }
}
