<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        Transaction::all()->each->delete();

        Transaction::factory()->count(20)->create();
    }
}
