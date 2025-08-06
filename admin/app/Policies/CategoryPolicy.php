<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(RolesEnum::SUPER_ADMIN->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(RolesEnum::SUPER_ADMIN->value);
    }
}
