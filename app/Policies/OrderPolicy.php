<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
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
     * Determine whether the user can view any orders.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'orders.view.all');
    }

    /**
     * Determine whether the user can view the order.
     *
     * @param User $user
     *
     * @param Order $order
     *
     * @return bool
     */
    public function view(User $user, Order $order): bool
    {
        return $this->anyOrOwn(
            $user,
            $order,
            'orders.view.all',
            'orders.view.own'
        );
    }

    /**
     * Determine whether the user can create orders.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'orders.create');
    }

    /**
     * Determine whether the user can update the order.
     *
     * @param User $user
     *
     * @param Order $order
     *
     * @return bool
     */
    public function update(User $user, Order $order): bool
    {
        return $this->anyOrOwn(
            $user,
            $order,
            'orders.update.any',
            'orders.update.own'
        );
    }

    /**
     * Determine whether the user can delete the order.
     *
     * @param User $user
     *
     * @param Order $order
     *
     * @return bool
     */
    public function delete(User $user, Order $order): bool
    {
        return $this->anyOrOwn(
            $user,
            $order,
            'orders.delete.any',
            'orders.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the order.
     *
     * @param User $user
     *
     * @param Order $order
     *
     * @return bool
     */
    public function restore(User $user, Order $order): bool
    {
        return $this->anyOrOwn(
            $user,
            $order,
            'orders.restore.any',
            'orders.restore.own'
        );
    }
}
