<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Deleting a category is restricted to super-admins, and refused outright
     * while anything still points at it.
     *
     * Both `products.category_id` and `categories.parent_id` restrict on
     * delete, so the database would reject these anyway — but as a raw query
     * error in the panel. Checking here turns that into a delete button that
     * simply is not offered.
     */
    public function delete(User $user, Category $category): bool
    {
        if (! $user->hasRole(RolesEnum::SUPER_ADMIN->value)) {
            return false;
        }

        return ! $category->children()->exists()
            && ! $category->products()->exists();
    }

    /**
     * Bulk delete cannot inspect the selection up front, so it stays a
     * role check; individual rows are still protected by the constraints.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasRole(RolesEnum::SUPER_ADMIN->value);
    }
}
