<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class DealPolicy
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
        return $this->has($user, 'deals.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Deal $deal
     *
     * @return bool
     */
    public function view(User $user, Deal $deal): bool
    {
        return $this->anyOrOwn(
            $user,
            $deal,
            'deals.view.all',
            'deals.view.own'
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
        return $this->has($user, 'deals.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Deal $deal
     *
     * @return bool
     */
    public function update(User $user, Deal $deal): bool
    {
        return $this->anyOrOwn(
            $user,
            $deal,
            'deals.update.any',
            'deals.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Deal $deal
     *
     * @return bool
     */
    public function delete(User $user, Deal $deal): bool
    {
        return $this->anyOrOwn(
            $user,
            $deal,
            'deals.delete.any',
            'deals.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Deal $deal
     *
     * @return bool
     */
    public function restore(User $user, Deal $deal): bool
    {
        return $this->anyOrOwn(
            $user,
            $deal,
            'deals.restore.any',
            'deals.restore.own'
        );
    }
}
