<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AttributeGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {

        //        dd(AttributeGroup::query()->first()->id);
        $attributes = [
            [
                'attribute_group_id' => 1,
                'value' => 'قرمز',
            ],
            [
                'attribute_group_id' => 1,
                'value' => 'آبی',
            ],
            [
                'attribute_group_id' => 2,
                'value' => '8GB RAM',
            ],
            [
                'attribute_group_id' => 2,
                'value' => '16GB RAM',
            ],
        ];

        DB::table('attributes')->insertOrIgnore($attributes);

    }
}
