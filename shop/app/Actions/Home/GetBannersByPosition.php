<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Actions\Catalog\TransformImage;
use App\Enums\BannerPositionEnum;
use App\Models\Banner;

class GetBannersByPosition
{
    public function __construct(private TransformImage $transformImage) {}

    /**
     * Published banners assigned to a given position, ordered by `sort`. The
     * caller decides whether to render them as a grid (all) or a single
     * banner (the first). The position is a BannerPositionEnum (shared with
     * the admin) rather than a loose string, so the admin's choice and this
     * lookup can't drift apart.
     *
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(BannerPositionEnum $position): array
    {
        return Banner::query()
            ->published()
            ->where('position', $position->value)
            ->with('featuredImage')
            ->orderBy('sort')
            ->get()
            ->map(fn (Banner $banner): array => [
                'id' => $banner->id,
                'heading' => $banner->heading,
                'url' => $banner->url,
                'image' => ($this->transformImage)($banner->featuredImage)?->toArray(),
            ])
            ->all();
    }
}
