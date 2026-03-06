<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
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
     * Determine whether the user can view any tasks.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tasks.view.all');
    }

    /**
     * Determine whether the user can view the task.
     *
     * @param User $user
     *
     * @param Task $task
     *
     * @return bool
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->hasPermission('tasks.view.all')) {
            return true;
        }

        return $user->hasPermission('tasks.view.own') &&
            $task->created_by === $user->id;
    }

    /**
     * Determine whether the user can create tasks.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('tasks.create');
    }

    /**
     * Determine whether the user can update the task.
     *
     * @param User $user
     *
     * @param Task $task
     *
     * @return bool
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasPermission('tasks.update.any') ||
            ($user->hasPermission(
                'tasks.update.own'
            ) && $task->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the task.
     *
     * @param User $user
     *
     * @param Task $task
     *
     * @return bool
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->hasPermission('tasks.delete.any') ||
        ($user->hasPermission('tasks.delete.own') &&
            $task->created_by === $user->id);
    }
}
