<?php

namespace App\Policies;

use App\Models\BillOfMaterial;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class BillOfMaterialPolicy
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
        return $this->has($user, 'billOfMaterials.view.all');
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
        return $this->has($user, 'billOfMaterials.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param BillOfMaterial $billOfMaterial
     *
     * @return bool
     */
    public function update(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $this->anyOrOwn(
            $user,
            $billOfMaterial,
            'billOfMaterials.update.any',
            'billOfMaterials.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param BillOfMaterial $billOfMaterial
     *
     * @return bool
     */
    public function delete(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $this->anyOrOwn(
            $user,
            $billOfMaterial,
            'billOfMaterials.delete.any',
            'billOfMaterials.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param BillOfMaterial $billOfMaterial
     *
     * @return bool
     */
    public function restore(User $user, BillOfMaterial $billOfMaterial): bool
    {
        return $this->anyOrOwn(
            $user,
            $billOfMaterial,
            'billOfMaterials.restore.any',
            'billOfMaterials.restore.own'
        );
    }
}
