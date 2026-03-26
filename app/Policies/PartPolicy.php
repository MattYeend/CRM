<?php

namespace App\Policies;

use App\Models\Part;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartPolicy
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
        return $this->has($user, 'parts.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Part $part
     *
     * @return bool
     */
    public function view(User $user, Part $part): bool
    {
        return $this->anyOrOwn(
            $user,
            $part,
            'parts.view.all',
            'parts.view.own'
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
        return $this->has($user, 'parts.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Part $part
     *
     * @return bool
     */
    public function update(User $user, Part $part): bool
    {
        return $this->anyOrOwn(
            $user,
            $part,
            'parts.update.any',
            'parts.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Part $part
     *
     * @return bool
     */
    public function delete(User $user, Part $part): bool
    {
        return $this->anyOrOwn(
            $user,
            $part,
            'parts.delete.any',
            'parts.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Part $part
     *
     * @return bool
     */
    public function restore(User $user, Part $part): bool
    {
        return $this->anyOrOwn(
            $user,
            $part,
            'parts.restore.any',
            'parts.restore.own'
        );
    }
}
