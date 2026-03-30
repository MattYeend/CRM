<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
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
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'notes.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Note $note
     *
     * @return bool
     */
    public function view(User $user, Note $note): bool
    {
        return $this->anyOrOwn(
            $user,
            $note,
            'notes.view.all',
            'notes.view.own'
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'notes.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Note $note
     *
     * @return bool
     */
    public function update(User $user, Note $note): bool
    {
        return $this->anyOrOwn(
            $user,
            $note,
            'notes.update.any',
            'notes.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Note $note
     *
     * @return bool
     */
    public function delete(User $user, Note $note): bool
    {
        return $this->anyOrOwn(
            $user,
            $note,
            'notes.delete.any',
            'notes.delete.own'
        );
    }

    /**
     * Determine wheter the user can restore the model.
     *
     * @param User $user
     *
     * @param Note $note
     *
     * @return bool
     */
    public function restore(User $user, Note $note): bool
    {
        return $this->anyOrOwn(
            $user,
            $note,
            'notes.restore.any',
            'notes.restore.own'
        );
    }
}
