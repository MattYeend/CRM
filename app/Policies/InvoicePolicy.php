<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
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
        return $user->hasPermission('invoices.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('invoices.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoices.update.any') ||
            ($user->hasPermission(
                'invoices.update.own'
            ) && $invoice->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('invoices.delete');
    }
}
