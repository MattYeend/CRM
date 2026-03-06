<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DealPolicy
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
     * Determine whether the user can view any deals.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('deals.view.all');
    }

    /**
     * Determine whether the user can view the deal.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermission('deals.view');
    }

    /**
     * Determine whether the user can create deals.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('deals.create');
    }

    /**
     * Determine whether the user can update the deal.
     *
     * @param User $user
     *
     * @param Deal $deal
     *
     * @return bool
     */
    public function update(User $user, Deal $deal): bool
    {
        return $user->hasPermission('deals.update.any') ||
            ($user->hasPermission(
                'deals.update.own'
            ) && $deal->owner_id === $user->id);
    }

    /**
     * Determine whether the user can delete the deal.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->hasPermission('deals.delete');
    }
}
