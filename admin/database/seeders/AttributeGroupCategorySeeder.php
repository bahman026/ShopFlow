<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AttributeGroupCategory;
use Illuminate\Database\Seeder;

class AttributeGroupCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'attribute_group_id' => 1,
                'category_id' => 1,
                'as_filter' => true,
                'required' => true,
            ],
            [
                'attribute_group_id' => 2,
                'category_id' => 2,
                'as_filter' => true,
                'required' => false,
            ],
        ];

        foreach ($data as $item) {
            AttributeGroupCategory::query()->updateOrCreate(
                [
                    'attribute_group_id' => $item['attribute_group_id'],
                    'category_id' => $item['category_id'],
                ],
                $item
            );
        }
    }
}
