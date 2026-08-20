<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

/**
 * What a permission lets a role do within a PermissionGroupEnum.
 */
enum PermissionActionEnum: string
{
    use HasOptions;

    case VIEW = 'view';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => trans('permission.action_view'),
            self::CREATE => trans('permission.action_create'),
            self::UPDATE => trans('permission.action_update'),
            self::DELETE => trans('permission.action_delete'),
        };
    }

    /**
     * The permission name stored in the `permissions` table, e.g. `edit`
     * within `orders` becomes `update_orders`.
     */
    public function for(PermissionGroupEnum $group): string
    {
        return $this->value . '_' . $group->value;
    }

    /**
     * Every permission name this application recognises.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        $names = [];

        foreach (PermissionGroupEnum::cases() as $group) {
            foreach (self::cases() as $action) {
                $names[] = $action->for($group);
            }
        }

        return $names;
    }
}
