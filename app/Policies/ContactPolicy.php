<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
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
     * Determine whether the user can view any contacts.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'contacts.view.all');
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
        return $this->anyOrOwn(
            $user,
            $contact,
            'contacts.view.all',
            'contacts.view.own'
        );
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
        return $this->has($user, 'contacts.create');
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
        return $this->anyOrOwn(
            $user,
            $contact,
            'contacts.update.any',
            'contacts.update.own'
        );
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
        return $this->anyOrOwn(
            $user,
            $contact,
            'contacts.delete.any',
            'contacts.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the contact.
     *
     * @param User $user
     *
     * @param Contact $contact
     *
     * @return bool
     */
    public function restore(User $user, Contact $contact): bool
    {
        return $this->anyOrOwn(
            $user,
            $contact,
            'contacts.restore.any',
            'contacts.restore.own'
        );
    }
}
