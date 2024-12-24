<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeGroup;
// Replace with your model name
use Illuminate\Database\Seeder;

// Replace with your model name

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributeGroupsData = [
            [
                'ancestor_id' => 1,
                'name' => 'رنگ',
                'label' => 'انتخاب رنگ',
                'order' => 1,
                'attributes' => [
                    ['value' => 'قرمز'],
                    ['value' => 'آبی'],
                ],
            ],
            [
                'ancestor_id' => 1,
                'name' => 'حافظه',
                'label' => 'مقدار حافظه',
                'order' => 2,
                'attributes' => [
                    ['value' => '8GB'],
                    ['value' => '16GB'],
                ],
            ],
            [
                'ancestor_id' => 2,
                'name' => 'ابعاد',
                'label' => 'اندازه‌های محصول',
                'order' => 1,
            ],
            [
                'ancestor_id' => 2,
                'name' => 'وزن',
                'label' => 'وزن محصول',
                'order' => 2,
            ],
        ];

        foreach ($attributeGroupsData as $groupData) {
            // Handle the AttributeGroup
            $attributeGroup = AttributeGroup::query()->updateOrCreate(
                [
                    'name' => $groupData['name'], // Unique identifier for update or create
                ],
                [
                    'ancestor_id' => $groupData['ancestor_id'],
                    'label' => $groupData['label'],
                    'order' => $groupData['order'],
                ]
            );

            if (! empty($groupData['attributes'])) {
                foreach ($groupData['attributes'] as $attributeData) {
                    Attribute::query()->firstOrCreate(
                        [
                            'value' => $attributeData['value'],
                            'attribute_group_id' => $attributeGroup->id,
                        ],
                        [
                            'value' => $attributeData['value'],
                            'attribute_group_id' => $attributeGroup->id, ]
                    );
                }
            }
        }
    }
}
