<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SliderPositionEnum;
use App\Enums\SliderStatusEnum;
use App\Models\Slide;
use App\Models\Slider;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        // Delete slides one by one so each Slide's `deleting` event fires and
        // cleans up its image. Then delete sliders with a plain delete(), not
        // truncate(): Postgres refuses to TRUNCATE a table referenced by a FK
        // (slides.slider_id) even once the slides are gone.
        Slide::all()->each->delete();
        Slider::query()->delete();

        // One published slider per position, so every placement the storefront
        // knows about (SliderPositionEnum) has coherent demo data and the
        // storefront's "first published slider per position" lookup is
        // deterministic (no random duplicate positions fighting for the spot).
        foreach (SliderPositionEnum::cases() as $position) {
            $slider = Slider::factory()->create([
                'position' => $position->value,
                'status' => SliderStatusEnum::PUBLISHED,
            ]);

            Slide::factory()->count(5)->withImage()->create(['slider_id' => $slider->id]);
        }
    }
}
