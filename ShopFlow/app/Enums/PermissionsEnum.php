<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasOptions;

enum PermissionsEnum: string
{
    use HasOptions;

    // Basic Permissions
    case VIEW_POSTS = 'view_posts';
    case CREATE_POSTS = 'create_posts';
    case EDIT_POSTS = 'edit_posts';
    case DELETE_POSTS = 'delete_posts';

    // User Management Permissions
    case VIEW_USERS = 'view_users';
    case CREATE_USERS = 'create_users';
    case EDIT_USERS = 'edit_users';
    case DELETE_USERS = 'delete_users';

    // Settings Permissions
    case VIEW_SETTINGS = 'view_settings';
    case EDIT_SETTINGS = 'edit_settings';

    public function label(): string
    {
        return match ($this) {
            self::VIEW_POSTS => 'View Posts',
            self::CREATE_POSTS => 'Create Posts',
            self::EDIT_POSTS => 'Edit Posts',
            self::DELETE_POSTS => 'Delete Posts',
            self::VIEW_USERS => 'View Users',
            self::CREATE_USERS => 'Create Users',
            self::EDIT_USERS => 'Edit Users',
            self::DELETE_USERS => 'Delete Users',
            self::VIEW_SETTINGS => 'View Settings',
            self::EDIT_SETTINGS => 'Edit Settings',
        };
    }
}
