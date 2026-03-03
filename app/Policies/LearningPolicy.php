<?php

namespace App\Policies;

use App\Models\Learning;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningPolicy
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
        return $user->hasPermission('learnings.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('learnings.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('learnings.create');
    }

    public function update(User $user, Learning $learning): bool
    {
        return $user->hasPermission('learnings.update.any') ||
            ($user->hasPermission(
                'learnings.update.own'
            ) && $learning->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('learnings.delete');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('learnings.manage');
    }

    public function assign(User $user): bool
    {
        return $user->hasPermission('learnings.assign');
    }

    public function access(User $user): bool
    {
        return $user->hasPermission('learnings.access');
    }
}
