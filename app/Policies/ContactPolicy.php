<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
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
        return $user->hasPermission('contacts.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('contacts.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('contacts.create');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->hasPermission('contacts.update.any') ||
            ($user->hasPermission(
                'contacts.update.own'
            ) && $contact->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('contacts.delete');
    }
}
