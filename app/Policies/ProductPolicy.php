<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
        return $this->has($user, 'products.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Product $product
     *
     * @return bool
     */
    public function view(User $user, Product $product): bool
    {
        return $this->anyOrOwn(
            $user,
            $product,
            'products.view.all',
            'products.view.own'
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
        return $this->has($user, 'products.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Product $product
     *
     * @return bool
     */
    public function update(User $user, Product $product): bool
    {
        return $this->anyOrOwn(
            $user,
            $product,
            'products.update.any',
            'products.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Product $product
     *
     * @return bool
     */
    public function delete(User $user, Product $product): bool
    {
        return $this->anyOrOwn(
            $user,
            $product,
            'products.delete.any',
            'products.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Product $product
     *
     * @return bool
     */
    public function restore(User $user, Product $product): bool
    {
        return $this->anyOrOwn(
            $user,
            $product,
            'products.restore.any',
            'products.restore.own'
        );
    }
}
