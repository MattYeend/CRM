<?php

namespace App\Policies;

use App\Models\Learning;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the Learning model.
 *
 * This policy controls access to Learning models based on user permissions.
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
class LearningPolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other
     * permissions.
     *
     * @param  User $user
     *
     * @return bool|null Return true to allow, null to continue checking
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
     * @param  User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'learnings.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User $user
     * @param  Learning $learning
     *
     * @return bool
     */
    public function view(User $user, Learning $learning): bool
    {
        return $this->anyOrOwn(
            $user,
            $learning,
            'learnings.view.all',
            'learnings.view.own'
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'learnings.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  Learning $learning
     *
     * @return bool
     */
    public function update(User $user, Learning $learning): bool
    {
        return $this->anyOrOwn(
            $user,
            $learning,
            'learnings.update.any',
            'learnings.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  Learning $learning
     *
     * @return bool
     */
    public function delete(User $user, Learning $learning): bool
    {
        return $this->anyOrOwn(
            $user,
            $learning,
            'learnings.delete.any',
            'learnings.delete.own'
        );
    }

    /**
     * Determine whether the user can assign models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function assign(User $user): bool
    {
        return $this->has($user, 'learnings.assign');
    }

    /**
     * Determine whether the user can access models.
     *
     * @param  User  $user
     *
     * @return  bool
     */
    public function access(User $user): bool
    {
        return $this->has($user, 'learnings.access');
    }

    /**
     * Determine whether the user can complete their own models.
     *
     * @param  User $user
     * @param  Learning $learning
     *
     * @return bool
     */
    public function complete(User $user, Learning $learning)
    {
        return $this->anyOrOwn(
            $user,
            $learning,
            'learnings.complete.any',
            'learnings.complete.own'
        );
    }

    /**
     * Determine whether the user can incomplete their own models.
     *
     * @param  User $user
     * @param  Learning $learning
     *
     * @return bool
     */
    public function incomplete(User $user, Learning $learning)
    {
        return $this->anyOrOwn(
            $user,
            $learning,
            'learnings.incomplete.any',
            'learnings.incomplete.own'
        );
    }

    /**
     * Determine wheter the user can restore the model.
     *
     * @param  User $user
     * @param  Learning $learning
     *
     * @return bool
     */
    public function restore(User $user, Learning $learning): bool
    {
        return $this->anyOrOwn(
            $user,
            $learning,
            'learnings.restore.any',
            'learnings.restore.own'
        );
    }
}
