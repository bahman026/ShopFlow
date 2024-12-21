<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    const string ADMIN_EMAIL = 'admin@shopFlow.dev';

    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            [
                'email' => self::ADMIN_EMAIL,
            ],
            [
                'first_name' => 'john',
                'last_name' => 'doe',
                'email_verified_at' => now(),
                'password' => 'password',
                'status' => UserStatusEnum::ACTIVE->value,
            ]
        );
        $user->assignRole(RolesEnum::SUPER_ADMIN);
    }
}
