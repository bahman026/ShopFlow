<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Actions\Catalog\TransformImage;
use App\Models\Banner;

class GetPromoBanners
{
    /**
     * Admin-defined banner position the storefront home reads.
     */
    private const POSITION = 'home-middle';

    public function __construct(private TransformImage $transformImage) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return Banner::query()
            ->published()
            ->where('position', self::POSITION)
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
