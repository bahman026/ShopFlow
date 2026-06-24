<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserConfig;
use Illuminate\Database\Seeder;

class UserConfigSeeder extends Seeder
{
    public function run(): void
    {
        UserConfig::all()->each->delete();

        UserConfig::factory()->count(10)->create();
    }
}
