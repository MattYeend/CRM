<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the Invoice model.
 *
 * This policy controls access to Invoice models based on user permissions.
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
class InvoicePolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other
     * permissions.
     *
     * @param  User $user
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
     * @param  User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'invoices.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User $user
     * @param  Invoice $invoice
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
     * Determine whether the user can create models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'invoices.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  Invoice $invoice
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
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  Invoice $invoice
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

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  Invoice $invoice
     *
     * @return bool
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return $this->anyOrOwn(
            $user,
            $invoice,
            'invoices.restore.any',
            'invoices.restore.own'
        );
    }


    /**
     * Determine whether the user can assign models.
     *
     * @param  User  $user
     *
     * @return  bool
     */
    public function assign(User $user): bool
    {
        return $this->has($user, 'invoices.assign');
    }
}
