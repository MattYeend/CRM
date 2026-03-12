<?php

namespace App\Policies;

use App\Models\ProductDeal;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductDealPolicy
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
     * Determine whether the user can view any product deals.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'productDeals.view.all');
    }

    /**
     * Determine whether the user can view the product deal.
     *
     * @param User $user
     *
     * @param ProductDeal $productDeal
     *
     * @return bool
     */
    public function view(User $user, ProductDeal $productDeal): bool
    {
        return $this->anyOrOwn(
            $user,
            $productDeal,
            'productDeals.view.all',
            'productDeals.view.own'
        );
    }

    /**
     * Determine whether the user can create product deals.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'productDeals.create');
    }

    /**
     * Determine whether the user can update the product deal.
     *
     * @param User $user
     *
     * @param ProductDeal $productDeal
     *
     * @return bool
     */
    public function update(User $user, ProductDeal $productDeal): bool
    {
        return $this->anyOrOwn(
            $user,
            $productDeal,
            'productDeals.update.any',
            'productDeals.update.own'
        );
    }

    /**
     * Determine whether the user can delete the product deal.
     *
     * @param User $user
     *
     * @param ProductDeal $productDeal
     *
     * @return bool
     */
    public function delete(User $user, ProductDeal $productDeal): bool
    {
        return $this->anyOrOwn(
            $user,
            $productDeal,
            'productDeals.delete.any',
            'productDeals.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the product deal.
     *
     * @param User $user
     *
     * @param ProductDeal $productDeal
     *
     * @return bool
     */
    public function restore(User $user, ProductDeal $productDeal): bool
    {
        return $this->anyOrOwn(
            $user,
            $productDeal,
            'productDeals.restore.any',
            'productDeals.restore.own'
        );
    }
}
