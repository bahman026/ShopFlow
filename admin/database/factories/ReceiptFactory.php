<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReceiptTypeEnum;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'card_id' => null,
            'amount' => fake()->numberBetween(100_000, 10_000_000),
            'order_id' => null,
            'tracking_code' => fake()->optional()->numerify('##########'),
            'paid_at' => fake()->optional()->dateTimeThisYear(),
            'destination_name' => fake()->optional()->name(),
            'destination_bank' => fake()->optional()->company(),
            'end_of_card_number' => fake()->optional()->numerify('####'),
            'description' => fake()->optional()->sentence(),
            'is_paya' => fake()->boolean(),
            'type' => fake()->randomElement(ReceiptTypeEnum::cases()),
        ];
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (Receipt $receipt): void {
            $receipt->image()->create([
                'path' => ImageFactory::placeholderUrl(),
                'alt_text' => fake()->words(2, true),
            ]);
        });
    }
}
