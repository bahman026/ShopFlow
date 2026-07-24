<?php

declare(strict_types=1);

namespace App\Actions\Page;

use App\Actions\Catalog\TransformImage;
use App\DTOs\PageDTO;
use App\Models\Page;

class BuildPageDetail
{
    public function __construct(private TransformImage $transformImage) {}

    public function __invoke(Page $page): PageDTO
    {
        return new PageDTO(
            id: $page->id,
            heading: $page->heading,
            url: '/'.$page->slug,
            title: $page->title,
            description: $page->description,
            content: $page->content,
            noIndex: (bool) $page->no_index,
            canonical: $page->canonical,
            image: ($this->transformImage)($page->image),
        );
    }
}
