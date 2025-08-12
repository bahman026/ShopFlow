<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AttributeGroup;
use App\Models\AttributeGroupCategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeGroupCategoryFactory extends Factory
{
    protected $model = AttributeGroupCategory::class;

    public function definition(): array
    {
        return [
            'attribute_group_id' => AttributeGroup::factory(),
            'category_id' => Category::factory(),
            'as_filter' => $this->faker->boolean,
            'required' => $this->faker->boolean,
        ];
    }
}
