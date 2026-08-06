<?php

declare(strict_types=1);

namespace App\Actions\Tag;

use App\DTOs\TagDTO;
use App\Models\Tag;

class BuildTagDetail
{
    public function __invoke(Tag $tag): TagDTO
    {
        return new TagDTO(
            id: $tag->id,
            name: $tag->name,
            url: '/tags/'.$tag->slug,
            title: $tag->title,
            description: $tag->description,
            content: $tag->content,
            noIndex: (bool) $tag->no_index,
            canonical: $tag->canonical,
            categoryName: $tag->category?->heading,
            categoryUrl: $tag->category === null ? null : '/categories/'.$tag->category->slug,
        );
    }
}
