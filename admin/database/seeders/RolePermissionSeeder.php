<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PermissionActionEnum;
use App\Enums\PermissionGroupEnum;
use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RolesEnum::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        foreach (PermissionActionEnum::all() as $permission) {
            Permission::findOrCreate($permission);
        }

        // Super-admins run the shop: everything, including staff accounts,
        // payment gateways and settings.
        Role::query()
            ->whereName(RolesEnum::SUPER_ADMIN->value)
            ->first()
            ?->syncPermissions(PermissionActionEnum::all());

        // Admins do the day-to-day work. They can run the catalogue, content
        // and promotions outright, and process orders, shipping and customers
        // without being able to delete any of that history. Settings, payment
        // gateways and staff accounts stay with super-admins.
        Role::query()
            ->whereName(RolesEnum::ADMIN->value)
            ->first()
            ?->syncPermissions($this->adminPermissions());

        // `user` is the storefront customer role. It grants nothing in the
        // panel — User::canAccessPanel() already keeps customers out, and this
        // makes sure a stray role assignment cannot change that.
        Role::query()
            ->whereName(RolesEnum::USER->value)
            ->first()
            ?->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array<int, string>
     */
    private function adminPermissions(): array
    {
        $fullControl = [
            PermissionGroupEnum::CATALOG,
            PermissionGroupEnum::CONTENT,
            PermissionGroupEnum::MARKETING,
        ];

        $withoutDeleting = [
            PermissionGroupEnum::ORDERS,
            PermissionGroupEnum::SHIPPING,
            PermissionGroupEnum::CUSTOMERS,
        ];

        $permissions = [];

        foreach ($fullControl as $group) {
            foreach (PermissionActionEnum::cases() as $action) {
                $permissions[] = $action->for($group);
            }
        }

        foreach ($withoutDeleting as $group) {
            foreach ([PermissionActionEnum::VIEW, PermissionActionEnum::CREATE, PermissionActionEnum::UPDATE] as $action) {
                $permissions[] = $action->for($group);
            }
        }

        return $permissions;
    }
}
