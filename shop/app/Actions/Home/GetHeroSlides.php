<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Actions\Catalog\TransformImage;
use App\Models\Slider;

class GetHeroSlides
{
    /**
     * Admin-defined slider position the storefront home reads.
     */
    private const POSITION = 'home-main';

    public function __construct(private TransformImage $transformImage) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        $slider = Slider::query()
            ->published()
            ->where('position', self::POSITION)
            ->with(['slides' => fn ($query) => $query->orderBy('order'), 'slides.image'])
            ->first();

        if ($slider === null) {
            return [];
        }

        return $slider->slides
            ->map(fn ($slide): array => [
                'id' => $slide->id,
                'heading' => $slide->heading,
                'label' => $slide->label,
                'url' => $slide->url,
                'image' => ($this->transformImage)($slide->image)?->toArray(),
            ])
            ->all();
    }
}
