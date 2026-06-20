<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CouponForEnum;
use App\Enums\CouponStatusEnum;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => Str::upper(Str::random(8)),
            'amount' => fake()->numberBetween(1, 90),
            'min_price' => fake()->numberBetween(0, 10000),
            'max_discount' => fake()->numberBetween(1000, 50000),
            'total_used' => 0,
            'total_uses' => fake()->numberBetween(1, 100),
            'user_id' => null,
            'user_creator_id' => null,
            'status' => fake()->randomElement(CouponStatusEnum::cases()),
            'is_percent' => fake()->boolean(),
            'shipping' => fake()->boolean(),
            'is_for' => fake()->randomElement(CouponForEnum::cases()),
            'started_at' => null,
            'expired_at' => null,
        ];
    }
}
