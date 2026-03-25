<?php

namespace App\Policies;

use App\Models\PartCategory;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartCategoryPolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Handle all permissions for super admin role.
     *
     * @param User $user
     *
     * @return bool|null
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole(Role::ROLE_SUPER_ADMIN)) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'partCategories.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param PartCategory $partCategory
     *
     * @return bool
     */
    public function view(User $user, PartCategory $partCategory): bool
    {
        return $this->anyOrOwn(
            $user,
            $partCategory,
            'partCategories.view.all',
            'partCategories.view.own'
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'partCategories.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param PartCategory $partCategory
     *
     * @return bool
     */
    public function update(User $user, PartCategory $partCategory): bool
    {
        return $this->anyOrOwn(
            $user,
            $partCategory,
            'partCategories.update.any',
            'partCategories.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param PartCategory $partCategory
     *
     * @return bool
     */
    public function delete(User $user, PartCategory $partCategory): bool
    {
        return $this->anyOrOwn(
            $user,
            $partCategory,
            'partCategories.delete.any',
            'partCategories.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param PartCategory $partCategory
     *
     * @return bool
     */
    public function restore(User $user, PartCategory $partCategory): bool
    {
        return $this->anyOrOwn(
            $user,
            $partCategory,
            'partCategories.restore.any',
            'partCategories.restore.own'
        );
    }
}
