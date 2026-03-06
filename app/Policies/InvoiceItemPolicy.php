<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoiceItemPolicy
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
     * Determine whether the user can view any invoice items.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('invoiceItems.view.all');
    }

    /**
     * Determine whether the user can view the invoice item.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermission('invoiceItems.view');
    }

    /**
     * Determine whether the user can create invoice items.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('invoiceItems.create');
    }

    /**
     * Determine whether the user can update the invoice item.
     *
     * @param User $user
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return bool
     */
    public function update(User $user, InvoiceItem $invoiceItem): bool
    {
        return $user->hasPermission('invoiceItems.update.any') ||
            ($user->hasPermission(
                'invoiceItems.update.own'
            ) && $invoiceItem->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the invoice item.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->hasPermission('invoiceItems.delete');
    }
}
