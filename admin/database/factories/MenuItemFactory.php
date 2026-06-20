<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'name' => fake()->words(2, true),
            'url' => fake()->optional()->url(),
            'label' => fake()->optional()->word(),
            'order' => fake()->numberBetween(0, 20),
        ];
    }

    public function withParent(MenuItem $parent): static
    {
        return $this->state([
            'menu_id' => $parent->menu_id,
            'parent_id' => $parent->id,
        ]);
    }
}
