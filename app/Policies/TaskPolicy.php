<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
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
     * Determine whether the user can view any tasks.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'tasks.view.all');
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
        return $this->anyOrOwn(
            $user,
            $task,
            'tasks.view.all',
            'tasks.view.own'
        );
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
        return $this->has($user, 'tasks.create');
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
        return $this->anyOrOwn(
            $user,
            $task,
            'tasks.update.any',
            'tasks.update.own'
        );
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
        return $this->anyOrOwn(
            $user,
            $task,
            'tasks.delete.any',
            'tasks.delete.own'
        );
    }
}
