<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Actions\Catalog\TransformImage;
use App\Models\Tag;

class GetHomeTags
{
    public function __construct(private TransformImage $transformImage) {}

    /**
     * Featured tags for the home-page tag strip: those flagged "show on home",
     * ordered by their configured home order. Each is an image card linking to
     * its tag landing page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return Tag::query()
            ->onHome()
            ->with('image')
            ->orderBy('home_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Tag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'url' => '/tags/'.$tag->slug,
                'image' => ($this->transformImage)($tag->image)?->toArray(),
            ])
            ->all();
    }
}
