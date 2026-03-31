<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the Task model.
 *
 * This policy controls access to Task models based on user permissions.
 * It supports both global ("any") and ownership-based ("own") permissions
 * for viewing, creating, updating, deleting, and restoring models.
 *
 * Permission checks are delegated to shared helpers that determine whether
 * a user has a given permission, owns the model (via the `created_by` field),
 * or can act on any model versus only those they own.
 *
 * Super admin users are granted all abilities automatically via the
 * before() method.
 */
class TaskPolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other
     * permissions.
     *
     * @param User $user
     *
     * @return bool|null Return true to allow, null to continue checking
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
        return $this->has($user, 'tasks.view.all');
    }

    /**
     * Determine whether the user can view the model.
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
     * Determine whether the user can create models.
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
     * Determine whether the user can update the model.
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
     * Determine whether the user can delete the model.
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

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Task $task
     *
     * @return bool
     */
    public function restore(User $user, Task $task): bool
    {
        return $this->anyOrOwn(
            $user,
            $task,
            'tasks.restore.any',
            'tasks.restore.own'
        );
    }
}
