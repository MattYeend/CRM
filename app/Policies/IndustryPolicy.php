<?php

namespace App\Policies;

use App\Models\Industry;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the Industry model.
 *
 * This policy controls access to Industry models based on user permissions.
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
class IndustryPolicy
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
        return $this->has($user, 'industries.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User $user
     * @param  Instry $industry
     *
     * @return bool
     */
    public function view(User $user, Industry $industry): bool
    {
        return $this->anyOrOwn(
            $user,
            $industry,
            'industries.view.all',
            'industries.view.own'
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
        return $this->has($user, 'industries.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  Industry $industry
     *
     * @return bool
     */
    public function update(User $user, Industry $industry): bool
    {
        return $this->anyOrOwn(
            $user,
            $industry,
            'industries.update.any',
            'industries.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  Industry $industry
     *
     * @retur bool
     */
    public function delete(User $user, Industry $industry): bool
    {
        return $this->anyOrOwn(
            $user,
            $industry,
            'industries.delete.any',
            'industries.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User $user
     * @param  Industry $industry
     */
    public function restore(User $user, Industry $industry): bool
    {
        return $this->anyOrOwn(
            $user,
            $industry,
            'industries.restore.any',
            'industries.restore.own'
        );
    }

    /**
     * Determine whether the user can access the model.
     *
     * @param  User $user
     * @param  Industry $industry
     *
     * @return bool
     */
    public function access(User $user, Industry $industry): bool
    {
        return $this->anyOrOwn(
            $user,
            $industry,
            'industries.access.any',
            'industries.access.own'
        );
    }
}
