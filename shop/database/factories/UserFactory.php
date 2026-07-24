<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => '09'.fake()->unique()->numerify('#########'),
            'mobile_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => UserStatusEnum::ACTIVE,
        ];
    }

    /**
     * A blocked user who must not be able to authenticate.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => UserStatusEnum::BLOCK,
        ]);
    }
}
