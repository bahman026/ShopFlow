<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Slide;
use App\Models\Slider;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        Slide::all()->each->delete();
        Slider::query()->truncate();

        Slider::factory()->count(10)->create()->each(function (Slider $slider): void {
            Slide::factory()->count(5)->withImage()->create(['slider_id' => $slider->id]);
        });
    }
}
