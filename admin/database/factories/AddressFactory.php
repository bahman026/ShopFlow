<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
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
            'user_id' => User::query()->inRandomOrder()->first()->id,
            'phone' => $this->faker->phoneNumber(),
            'postal_code' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'description' => $this->faker->text(),
            'city_id' => City::factory()->create()->id,
            'prime' => $this->faker->boolean(),
        ];
    }
}
