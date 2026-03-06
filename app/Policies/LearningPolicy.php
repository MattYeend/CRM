<?php

namespace App\Policies;

use App\Models\Learning;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningPolicy
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
     * Determine whether the user can view any learnings.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('learnings.view.all');
    }

    /**
     * Determine whether the user can view the learning.
     *
     * @param User $user
     *
     * @param Learning $learning
     *
     * @return bool
     */
    public function view(User $user, Learning $learning): bool
    {
        if ($user->hasPermission('learnings.view.all')) {
            return true;
        }

        return $user->hasPermission('learnings.view.own') &&
            $learning->created_by === $user->id;
    }

    /**
     * Determine whether the user can create learnings.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('learnings.create');
    }

    /**
     * Determine whether the user can update the learning.
     *
     * @param User $user
     *
     * @param Learning $learning
     *
     * @return bool
     */
    public function update(User $user, Learning $learning): bool
    {
        return $user->hasPermission('learnings.update.any') ||
            ($user->hasPermission(
                'learnings.update.own'
            ) && $learning->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the learning.
     *
     * @param User $user
     *
     * @param Learning $learning
     *
     * @return bool
     */
    public function delete(User $user, Learning $learning): bool
    {
        return $user->hasPermission('learnings.delete.any') ||
        ($user->hasPermission('learnings.delete.own') &&
            $learning->created_by === $user->id);
    }

    /**
     * Determine whether the user can manage learnings.
     *
     * @param User $user
     *
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('learnings.manage');
    }

    /**
     * Determine whether the user can assign learnings.
     *
     * @param User $user
     *
     * @return bool
     */
    public function assign(User $user): bool
    {
        return $user->hasPermission('learnings.assign');
    }

    /**
     * Determine whether the user can access learnings.
     *
     * @param User $user
     *
     * @return bool
     */
    public function access(User $user): bool
    {
        return $user->hasPermission('learnings.access');
    }

    /**
     * Determine whether the user can complete their own learnings.
     *
     * @param User $user
     *
     * @param Learning $learning
     *
     * @return bool
     */
    public function complete(User $user, Learning $learning)
    {
        return $user->hasPermission('learnings.complete.any') ||
        ($user->hasPermission('learnings.complete.own') &&
            $learning->created_by === $user->id);
    }

    /**
     * Determine whether the user can incomplete their own learnings.
     *
     * @param User $user
     *
     * @param Learning $learning
     *
     * @return bool
     */
    public function incomplete(User $user, Learning $learning)
    {
        return $user->hasPermission('companies.incomplete.any') ||
        ($user->hasPermission('companies.incomplete.own') &&
            $learning->created_by === $user->id);
    }
}
