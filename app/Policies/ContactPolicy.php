<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
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
     * Determine whether the user can view any contacts.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('contacts.view.all');
    }

    /**
     * Determine whether the user can view the contact.
     *
     * @param User $user
     *
     * @param Contact $contact
     *
     * @return bool
     */
    public function view(User $user, Contact $contact): bool
    {
        if ($user->hasPermission('contacts.view.all')) {
            return true;
        }

        return $user->hasPermission('contacts.view.own') &&
            $contact->created_by === $user->id;
    }

    /**
     * Determine whether the user can create contacts.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('contacts.create');
    }

    /**
     * Determine whether the user can update the contact.
     *
     * @param User $user
     *
     * @param Contact $contact
     *
     * @return bool
     */
    public function update(User $user, Contact $contact): bool
    {
        return $user->hasPermission('contacts.update.any') ||
            ($user->hasPermission(
                'contacts.update.own'
            ) && $contact->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the contact.
     *
     * @param User $user
     *
     * @param Contact $contact
     *
     * @return bool
     */
    public function delete(User $user, Contact $contact): bool
    {
        return $user->hasPermission('contacts.delete.any') ||
            ($user->hasPermission('contacts.delete.own') &&
                $contact->created_by === $user->id);
    }
}
