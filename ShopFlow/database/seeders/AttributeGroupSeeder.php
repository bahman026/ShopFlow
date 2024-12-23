<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AttributeGroup;
use Illuminate\Database\Seeder;

class AttributeGroupSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'ancestor_id' => 1,
                'name' => 'رنگ',
                'label' => 'انتخاب رنگ',
                'order' => 1,
            ],
            [
                'ancestor_id' => 1,
                'name' => 'حافظه',
                'label' => 'مقدار حافظه',
                'order' => 2,
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

        foreach ($data as $item) {
            AttributeGroup::query()->updateOrCreate(
                ['name' => $item['name'], 'ancestor_id' => $item['ancestor_id']],
                $item
            );
        }
    }
}
