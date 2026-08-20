<?php

declare(strict_types=1);

namespace App\Actions\Layout;

use App\Actions\Catalog\TransformImage;
use App\Enums\SliderPositionEnum;
use App\Models\Slide;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Relations\Relation;

class GetSliderByPosition
{
    public function __construct(private TransformImage $transformImage) {}

    /**
     * The published slider assigned to a given position, as an ordered list of
     * its slides. Empty when no published slider is assigned there. The
     * position is a SliderPositionEnum (shared with the admin) rather than a
     * loose string, so the admin's choice and this lookup can't drift apart.
     *
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(SliderPositionEnum $position): array
    {
        $slider = Slider::query()
            ->published()
            ->where('position', $position->value)
            ->with(['slides' => fn (Relation $query) => $query->orderBy('order'), 'slides.image'])
            ->first();

        if ($slider === null) {
            return [];
        }

        return $slider->slides
            ->map(fn (Slide $slide): array => [
                'id' => $slide->id,
                'heading' => $slide->heading,
                'label' => $slide->label,
                'url' => $slide->url,
                'image' => ($this->transformImage)($slide->image)?->toArray(),
            ])
            ->all();
    }
}
