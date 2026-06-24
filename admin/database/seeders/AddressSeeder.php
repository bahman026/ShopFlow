<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        Address::query()->forceDelete();

        Address::factory()->count(10)->create();
    }
}
