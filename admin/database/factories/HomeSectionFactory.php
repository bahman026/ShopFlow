<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\HomeSectionTypeEnum;
use App\Models\HomeSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomeSection>
 */
class HomeSectionFactory extends Factory
{
    protected $model = HomeSection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => HomeSectionTypeEnum::CATEGORIES,
            'title' => null,
            'config' => null,
            'order' => fake()->numberBetween(0, 20),
            'status' => true,
        ];
    }
}
