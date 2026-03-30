<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        return $this->has($user, 'users.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param User $model
     *
     * @return bool
     */
    public function view(User $user, User $model): bool
    {
        return $this->anyOrOwn(
            $user,
            $model,
            'users.view.all',
            'users.view.own'
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
        return $this->has($user, 'users.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param User $model
     *
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        return $this->anyOrOwn(
            $user,
            $model,
            'users.update.any',
            'users.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param User $model
     *
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        return $this->anyOrOwn(
            $user,
            $model,
            'users.delete.any',
            'users.delete.own'
        );
    }

    /**
     * Determine whether the user can manage model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $this->has($user, 'users.manage');
    }

    /**
     * Determine whether the user can assign roles to the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function assignRoles(User $user): bool
    {
        return $this->has($user, 'users.assign.roles');
    }

    /**
     * Determine whether the user can assign permissions to the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function assignPermissions(User $user): bool
    {
        return $this->has($user, 'users.assign.permissions');
    }

    /**
     * Determine whether the user can restore models.
     *
     * @param User $user
     *
     * @param User $model
     *
     * @return bool
     */
    public function restore(User $user, User $model): bool
    {
        return $this->anyOrOwn(
            $user,
            $model,
            'users.restore.any',
            'users.restore.own'
        );
    }
}
