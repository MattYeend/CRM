<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

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
     * Determine whether the user can view any users.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view');
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermission('users.view');
    }

    /**
     * Determine whether the user can create users.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool
    {
        return $user->hasPermission('users.update.any') ||
            ($user->hasPermission(
                'users.update.own'
            ) && $user->id === $user->id);
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->hasPermission('users.delete');
    }

    /**
     * Determine whether the user can manage users.
     *
     * @param User $user
     *
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    /**
     * Determine whether the user can assign roles to users.
     *
     * @param User $user
     *
     * @return bool
     */
    public function assignRoles(User $user): bool
    {
        return $user->hasPermission('users.assign.roles');
    }

    /**
     * Determine whether the user can assign permissions to users.
     *
     * @param User $user
     *
     * @return bool
     */
    public function assignPermissions(User $user): bool
    {
        return $user->hasPermission('users.assign.permissions');
    }
}
