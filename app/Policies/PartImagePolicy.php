<?php

namespace App\Policies;

use App\Models\PartImage;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the PartImage model.
 *
 * This policy controls access to PartImage models based on user permissions.
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
class PartImagePolicy
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
        return $this->has($user, 'partImages.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User $user
     * @param  PartImage $partImage
     *
     * @return bool
     */
    public function view(User $user, PartImage $partImage): bool
    {
        return $this->anyOrOwn(
            $user,
            $partImage,
            'partImages.view.all',
            'partImages.view.own',
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
        return $this->has($user, 'partImages.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  PartImage $partImage
     *
     * @return bool
     */
    public function update(User $user, PartImage $partImage): bool
    {
        return $this->anyOrOwn(
            $user,
            $partImage,
            'partImages.update.any',
            'partImages.update.own',
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  PartImage $partImage
     *
     * @return bool
     */
    public function delete(User $user, PartImage $partImage): bool
    {
        return $this->anyOrOwn(
            $user,
            $partImage,
            'partImages.delete.any',
            'partImages.delete.own',
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User $user
     * @param  PartImage $partImage
     *
     * @return bool
     */
    public function restore(User $user, PartImage $partImage): bool
    {
        return $this->anyOrOwn(
            $user,
            $partImage,
            'partImages.restore.any',
            'partImages.restore.own',
        );
    }

    /**
     * Determine whether the user can access the model.
     *
     * @param  User $user
     * @param  PartImage $partImage
     *
     * @return bool
     */
    public function access(User $user, PartImage $partImage): bool
    {
        return $this->anyOrOwn(
            $user,
            $partImage,
            'partImages.access.any',
            'partImages.access.own'
        );
    }
}
