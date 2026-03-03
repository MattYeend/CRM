<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoiceItemPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        if ($user->hasRole(Role::ROLE_SUPER_ADMIN)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('invoiceItems.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('invoiceItems.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('invoiceItems.create');
    }

    public function update(User $user, InvoiceItem $invoiceItem): bool
    {
        return $user->hasPermission('invoiceItems.update.any') ||
            ($user->hasPermission(
                'invoiceItems.update.own'
            ) && $invoiceItem->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('invoiceItems.delete');
    }
}
