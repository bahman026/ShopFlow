<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (RolesEnum::options() as $value => $label) {
            Role::firstOrCreate(['name' => $value]);
        }

        foreach (PermissionsEnum::options() as $value => $label) {
            Permission::firstOrCreate(['name' => $value]);
        }

        $adminPermissions = [
            PermissionsEnum::VIEW_POSTS->value,
            PermissionsEnum::CREATE_POSTS->value,
            PermissionsEnum::EDIT_POSTS->value,
            PermissionsEnum::DELETE_POSTS->value,
        ];

        Role::query()
            ->whereName(RolesEnum::ADMIN->value)
            ->first()
            ?->syncPermissions($adminPermissions);

        $superAdminPermissions = array_merge($adminPermissions, [
            PermissionsEnum::VIEW_USERS->value,
            PermissionsEnum::CREATE_USERS->value,
            PermissionsEnum::EDIT_USERS->value,
            PermissionsEnum::DELETE_USERS->value,
            PermissionsEnum::VIEW_SETTINGS->value,
            PermissionsEnum::EDIT_SETTINGS->value,
        ]);

        Role::query()
            ->whereName(RolesEnum::SUPER_ADMIN->value)
            ->first()
            ?->syncPermissions($superAdminPermissions);
    }
}
