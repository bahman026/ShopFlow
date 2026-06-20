<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        Faq::all()->each->delete();

        Faq::factory()->count(20)->create();
    }
}
