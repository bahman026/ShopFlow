<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ancestor;
use Illuminate\Database\Seeder;

class AncestorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ancestors = [
            ['name' => 'مشخصات فنی', 'order' => 1],
            ['name' => 'ویژگی‌های ظاهری', 'order' => 2],
        ];

        foreach ($ancestors as $ancestor) {
            Ancestor::query()->updateOrCreate(
                ['name' => $ancestor['name']],
                ['order' => $ancestor['order']]
            );
        }
    }
}
