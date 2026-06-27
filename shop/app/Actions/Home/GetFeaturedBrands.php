<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Actions\Catalog\TransformImage;
use App\Models\Brand;

class GetFeaturedBrands
{
    /**
     * How many brands the strip shows.
     */
    private const LIMIT = 12;

    public function __construct(private TransformImage $transformImage) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return Brand::query()
            ->active()
            ->with('image')
            ->orderBy('heading')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Brand $brand): array => [
                'id' => $brand->id,
                'heading' => $brand->heading,
                'url' => '/brands/'.$brand->slug,
                'image' => ($this->transformImage)($brand->image)?->toArray(),
            ])
            ->all();
    }
}
