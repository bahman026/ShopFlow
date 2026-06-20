<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        Wishlist::all()->each->delete();

        Wishlist::factory()->count(20)->create();
    }
}
