<?php

namespace App\Policies;

use App\Models\PartStockMovement;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartStockMovementPolicy
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
        return $this->has($user, 'partStockMovements.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param PartStockMovement $partStockMovement
     *
     * @return bool
     */
    public function view(User $user, PartStockMovement $partStockMovement): bool
    {
        return $this->anyOrOwn(
            $user,
            $partStockMovement,
            'partStockMovements.view.all',
            'partStockMovements.view.own'
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
        return $this->has($user, 'partStockMovements.create');
    }
}
