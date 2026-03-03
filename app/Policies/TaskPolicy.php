<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
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
        return $user->hasPermission('tasks.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('tasks.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasPermission('tasks.update.any') ||
            ($user->hasPermission(
                'tasks.update.own'
            ) && $task->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('tasks.delete');
    }
}
