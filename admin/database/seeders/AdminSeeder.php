<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('admin.account');

        $user = User::query()->firstOrCreate(
            [
                'email' => $admin['email'],
            ],
            [
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name'],
                'email_verified_at' => now(),
                'password' => $admin['password'],
                'status' => UserStatusEnum::ACTIVE->value,
            ]
        );
        $user->assignRole(RolesEnum::SUPER_ADMIN);
    }
}
