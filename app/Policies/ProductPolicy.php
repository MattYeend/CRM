<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the Product model.
 *
 * This policy controls access to Product models based on user permissions.
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
class ProductPolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other
     * permissions.
     *
     * @param User $user
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
