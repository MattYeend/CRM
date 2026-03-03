<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
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
        return $user->hasPermission('notes.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('notes.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('notes.create');
    }

    public function update(User $user, Note $note): bool
    {
        return $user->hasPermission('notes.update.any') ||
            ($user->hasPermission(
                'notes.update.own'
            ) && $note->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('notes.delete');
    }
}
