<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
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
        return $this->has($user, 'suppliers.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Supplier $supplier
     *
     * @return bool
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return $this->anyOrOwn(
            $user,
            $supplier,
            'suppliers.view.all',
            'suppliers.view.own'
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
        return $this->has($user, 'suppliers.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Supplier $supplier
     *
     * @return bool
     */
    public function update(User $user, Supplier $supplier): bool
    {
        return $this->anyOrOwn(
            $user,
            $supplier,
            'suppliers.update.any',
            'suppliers.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Supplier $supplier
     *
     * @return bool
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return $this->anyOrOwn(
            $user,
            $supplier,
            'suppliers.delete.any',
            'suppliers.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Supplier $supplier
     *
     * @return bool
     */
    public function restore(User $user, Supplier $supplier): bool
    {
        return $this->anyOrOwn(
            $user,
            $supplier,
            'suppliers.restore.any',
            'suppliers.restore.own'
        );
    }
}
