<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\PermissionActionEnum;
use App\Enums\PermissionGroupEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Gates a Filament resource behind the role's permissions.
 *
 * Without this, Filament falls back to the model's policy — and with almost no
 * policies registered, every panel user had full access to every resource
 * while `PermissionsEnum` sat unused. A resource opts in by declaring which
 * PermissionGroupEnum it belongs to.
 *
 * Where a policy *does* exist it still applies: both the permission and the
 * policy have to allow the action. That keeps hand-written rules like
 * CategoryPolicy (super-admins only, and not while children or products point
 * at the category) rather than replacing them with a coarser check.
 */
trait AuthorizesWithPermissions
{
    abstract public static function permissionGroup(): PermissionGroupEnum;

    public static function canViewAny(): bool
    {
        return static::allows(PermissionActionEnum::VIEW, 'viewAny');
    }

    public static function canView(Model $record): bool
    {
        return static::allows(PermissionActionEnum::VIEW, 'view', $record);
    }

    public static function canCreate(): bool
    {
        return static::allows(PermissionActionEnum::CREATE, 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::allows(PermissionActionEnum::UPDATE, 'update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::allows(PermissionActionEnum::DELETE, 'delete', $record);
    }

    public static function canDeleteAny(): bool
    {
        return static::allows(PermissionActionEnum::DELETE, 'deleteAny');
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::allows(PermissionActionEnum::DELETE, 'forceDelete', $record);
    }

    public static function canRestore(Model $record): bool
    {
        return static::allows(PermissionActionEnum::UPDATE, 'restore', $record);
    }

    /**
     * The role must hold the permission, and any policy registered for the
     * model must agree.
     */
    protected static function allows(PermissionActionEnum $action, string $ability, ?Model $record = null): bool
    {
        $user = Auth::user();

        if ($user === null || ! $user->can($action->for(static::permissionGroup()))) {
            return false;
        }

        return static::policyAllows($ability, $record);
    }

    /**
     * True unless a policy explicitly governs this ability.
     *
     * The `method_exists` check matters: Laravel denies an ability outright
     * when a policy exists but does not implement it, so consulting the policy
     * for everything would make a partial policy — CategoryPolicy defines only
     * delete — silently forbid viewing and editing categories too.
     */
    protected static function policyAllows(string $ability, ?Model $record): bool
    {
        $model = $record ?? static::getModel();
        $policy = Gate::getPolicyFor($model);

        if ($policy === null || ! method_exists($policy, $ability)) {
            return true;
        }

        return Gate::allows($ability, $record ?? static::getModel());
    }
}
