<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionPortEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(TransactionStatusEnum::cases());

        return [
            'user_id' => User::factory(),
            'order_id' => null,
            'ref_id' => fake()->optional()->numerify('############'),
            'amount' => fake()->numberBetween(100_000, 10_000_000),
            'accounting_id' => null,
            'port' => fake()->randomElement(TransactionPortEnum::cases()),
            'transaction_id' => fake()->optional()->numerify('##########'),
            'status' => $status,
            'ip' => fake()->ipv4(),
            'description' => fake()->optional()->sentence(),
            'paid_at' => $status === TransactionStatusEnum::SUCCESS ? fake()->dateTimeThisYear() : null,
            'result_code' => null,
            'result_message' => null,
        ];
    }

    public function success(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TransactionStatusEnum::SUCCESS,
            'paid_at' => fake()->dateTimeThisYear(),
        ]);
    }
}
