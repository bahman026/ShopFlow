<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Gateway;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    public function run(): void
    {
        Gateway::all()->each->delete();

        Gateway::factory()->count(5)->create();
    }
}
