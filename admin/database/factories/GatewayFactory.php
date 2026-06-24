<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GatewayForEnum;
use App\Models\Gateway;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gateway>
 */
class GatewayFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'name' => fake()->company(),
            'for' => fake()->randomElement(GatewayForEnum::cases()),
            'username' => fake()->optional()->userName(),
            'password' => fake()->optional()->password(),
            'gate_id' => fake()->optional()->numerify('##########'),
            'active' => fake()->boolean(80),
            'priority' => fake()->numberBetween(0, 100),
            'coding' => fake()->optional()->word(),
        ];
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (Gateway $gateway): void {
            $gateway->image()->create([
                'path' => fake()->imageUrl(),
                'alt_text' => fake()->words(2, true),
            ]);
        });
    }
}
