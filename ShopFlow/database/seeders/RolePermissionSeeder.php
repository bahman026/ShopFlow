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
        Role::query()->truncate();
        Permission::query()->truncate();

        foreach (RolesEnum::options() as $key => $role) {
            Role::create(['name' => $key]);
        }

        foreach (PermissionsEnum::options() as $key => $permission) {
            Permission::create(['name' => $key]);
        }

        $admin = Role::query()->whereName(RolesEnum::USER)->first();
        $adminPermissions = [
            PermissionsEnum::CREATE_POSTS->value,
            PermissionsEnum::DELETE_POSTS->value,
            PermissionsEnum::EDIT_POSTS->value,
            PermissionsEnum::VIEW_POSTS->value,
        ];
        $admin->syncPermissions($adminPermissions);

        $superAdmin = Role::query()->whereName(RolesEnum::SUPER_ADMIN)->first();
        $superAdminPermissions = [
            PermissionsEnum::CREATE_USERS->value,
            PermissionsEnum::DELETE_USERS->value,
            PermissionsEnum::VIEW_USERS->value,
            PermissionsEnum::EDIT_USERS->value,
        ];
        $superAdminPermissions = array_merge($superAdminPermissions, $adminPermissions);
        $superAdmin->syncPermissions($superAdminPermissions);

    }
}
