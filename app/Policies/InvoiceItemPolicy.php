<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the InvoiceItem model.
 *
 * This policy controls access to InvoiceItem models based on user permissions.
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
class InvoiceItemPolicy
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
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'invoiceItems.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User $user
     * @param  InvoiceItem $invoiceItem
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
     * Determine whether the user can create models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'invoiceItems.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  InvoiceItem $invoiceItem
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
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  InvoiceItem $invoiceItem
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
     * Determine whether the user can restore the model.
     *
     * @param  User $user
     * @param  InvoiceItem $invoiceItem
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


    /**
     * Determine whether the user can assign models.
     *
     * @param  User  $user
     *
     * @return  bool
     */
    public function assign(User $user): bool
    {
        return $this->has($user, 'invoiceItems.assign');
    }
}
