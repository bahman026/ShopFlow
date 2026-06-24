<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserConfig>
 */
class UserConfigFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'key' => fake()->unique()->slug(2),
            'label' => fake()->optional()->words(2, true),
            'content' => fake()->optional()->sentence(),
            'autoload' => fake()->boolean(),
        ];
    }
}
