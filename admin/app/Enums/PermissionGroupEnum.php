<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

/**
 * The areas of the panel a role can be granted access to.
 *
 * Permissions are one of these groups crossed with a PermissionActionEnum, so
 * granting a role "edit_orders" covers every order-shaped resource at once
 * rather than needing one permission per Filament resource.
 */
enum PermissionGroupEnum: string
{
    use HasOptions;

    case CATALOG = 'catalog';
    case CONTENT = 'content';
    case ORDERS = 'orders';
    case CUSTOMERS = 'customers';
    case SHIPPING = 'shipping';
    case MARKETING = 'marketing';
    case SETTINGS = 'settings';

    public function label(): string
    {
        return match ($this) {
            self::CATALOG => trans('permission.group_catalog'),
            self::CONTENT => trans('permission.group_content'),
            self::ORDERS => trans('permission.group_orders'),
            self::CUSTOMERS => trans('permission.group_customers'),
            self::SHIPPING => trans('permission.group_shipping'),
            self::MARKETING => trans('permission.group_marketing'),
            self::SETTINGS => trans('permission.group_settings'),
        };
    }
}
