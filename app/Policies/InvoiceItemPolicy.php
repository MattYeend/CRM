<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoiceItemPolicy
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
     * Determine whether the user can view any invoice items.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'invoiceItems.view.all');
    }

    /**
     * Determine whether the user can view the invoice item.
     *
     * @param User $user
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return bool
     */
    public function view(User $user, InvoiceItem $invoiceItem): bool
    {
        return $this->anyOrOwn(
            $user,
            $invoiceItem,
            'invoiceItems.view.all',
            'invoiceItems.view.own'
        );
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
        return $this->has($user, 'invoiceItems.create');
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
        return $this->anyOrOwn(
            $user,
            $invoiceItem,
            'invoiceItems.update.any',
            'invoiceItems.update.own'
        );
    }

    /**
     * Determine whether the user can delete the invoice item.
     *
     * @param User $user
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return bool
     */
    public function delete(User $user, InvoiceItem $invoiceItem): bool
    {
        return $this->anyOrOwn(
            $user,
            $invoiceItem,
            'invoiceItems.delete.any',
            'invoiceItems.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the invoice item.
     *
     * @param User $user
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return bool
     */
    public function restore(User $user, InvoiceItem $invoiceItem): bool
    {
        return $this->anyOrOwn(
            $user,
            $invoiceItem,
            'invoiceItems.restore.any',
            'invoiceItems.restore.own'
        );
    }
}
