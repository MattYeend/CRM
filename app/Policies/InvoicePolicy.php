<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
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
     * Determine whether the user can view any invoices.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'invoices.view.all');
    }

    /**
     * Determine whether the user can view the invoice.
     *
     * @param User $user
     *
     * @param Invoice $invoice
     *
     * @return bool
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $this->anyOrOwn(
            $user,
            $invoice,
            'invoices.view.all',
            'invoices.view.own'
        );
    }

    /**
     * Determine whether the user can create invoices.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'invoices.create');
    }

    /**
     * Determine whether the user can update the invoice.
     *
     * @param User $user
     *
     * @param Invoice $invoice
     *
     * @return bool
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $this->anyOrOwn(
            $user,
            $invoice,
            'invoices.update.any',
            'invoices.update.own'
        );
    }

    /**
     * Determine whether the user can delete the invoice.
     *
     * @param User $user
     *
     * @param Invoice $invoice
     *
     * @return bool
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->anyOrOwn(
            $user,
            $invoice,
            'invoices.delete.any',
            'invoices.delete.own'
        );
    }
}
