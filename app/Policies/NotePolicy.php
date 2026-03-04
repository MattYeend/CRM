<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
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
     * Determine whether the user can view any notes.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('notes.view');
    }

    /**
     * Determine whether the user can view the note.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermission('notes.view');
    }

    /**
     * Determine whether the user can create notes.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('notes.create');
    }

    /**
     * Determine whether the user can update the note.
     *
     * @param User $user
     * @param Note $note
     *
     * @return bool
     */
    public function update(User $user, Note $note): bool
    {
        return $user->hasPermission('notes.update.any') ||
            ($user->hasPermission(
                'notes.update.own'
            ) && $note->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the note.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->hasPermission('notes.delete');
    }
}
