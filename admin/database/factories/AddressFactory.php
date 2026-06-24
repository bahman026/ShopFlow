<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'user_id' => User::factory(),
            'phone' => $this->faker->phoneNumber(),
            'postal_code' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'description' => $this->faker->text(),
            'city_id' => City::factory(),
            'prime' => false,
        ];
    }

    public function prime(): self
    {
        return $this->state(fn (array $attributes): array => [
            'prime' => true,
        ]);
    }
}
