<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
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
     * Determine whether the user can view any permissions.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'permissions.view.all');
    }

    /**
     * Determine whether the user can view the permission.
     *
     * @param User $user
     *
     * @param Permission $permission
     *
     * @return bool
     */
    public function view(User $user, Permission $permission): bool
    {
        return $this->anyOrOwn(
            $user,
            $permission,
            'permissions.view.all',
            'permissions.view.own'
        );
    }

    /**
     * Determine whether the user can create permissions.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'permissions.create');
    }

    /**
     * Determine whether the user can update the permission.
     *
     * @param User $user
     *
     * @param Permission $permission
     *
     * @return bool
     */
    public function update(User $user, Permission $permission): bool
    {
        return $this->anyOrOwn(
            $user,
            $permission,
            'permissions.update.any',
            'permissions.update.own'
        );
    }

    /**
     * Determine whether the user can delete the permission.
     *
     * @param User $user
     *
     * @param Permission $permission
     *
     * @return bool
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $this->anyOrOwn(
            $user,
            $permission,
            'permissions.delete.any',
            'permissions.delete.own'
        );
    }

    /**
     * Determine wheter the user can restore the permissions.
     *
     * @param User $user
     *
     * @param Permission $permission
     *
     * @return bool
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $this->anyOrOwn(
            $user,
            $permission,
            'permissions.restore.any',
            'permissions.restore.own'
        );
    }
}
