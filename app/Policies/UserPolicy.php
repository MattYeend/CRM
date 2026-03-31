<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the User model.
 *
 * This policy controls access to User models based on user permissions.
 * It supports both global ("any") and ownership-based ("own") permissions
 * for viewing, creating, updating, deleting, and restoring models.
 *
 * Permission checks are delegated to shared helpers that determine whether
 * a user has a given permission, owns the model (via the `created_by` field),
 * or can act on any model versus only those they own.
 *
 * Super admin users are granted all abilities automatically via the
 * before() method.
 */
class UserPolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other
     * permissions.
     *
     * @param  User  $user
     *
     * @return  bool|null  Return true to allow, null to continue checking
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
     * @param  User  $user
     *
     * @return  bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'users.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return  bool
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
     * @param  User  $user
     *
     * @return  bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'users.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return  bool
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
     * @param  User  $user
     *
     * @return  bool
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
     * @param  User  $user
     *
     * @return  bool
     */
    public function assignPermissions(User $user): bool
    {
        return $this->has($user, 'users.assign.permissions');
    }

    /**
     * Determine whether the user can restore models.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return  bool
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
