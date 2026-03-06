<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
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
     * Determine whether the user can view any activities.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('activities.view.all');
    }

    /**
     * Determine whether the user can view the activity.
     *
     * @param User $user
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function view(User $user, Activity $activity): bool
    {
        if ($user->hasPermission('activities.view.all')) {
            return true;
        }

        return $user->hasPermission('activities.view.own') &&
            $activity->created_by === $user->id;
    }

    /**
     * Determine whether the user can create activities.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('activities.create');
    }

    /**
     * Determine whether the user can update the activity.
     *
     * @param User $user
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function update(User $user, Activity $activity): bool
    {
        return $user->hasPermission('activities.update.any') ||
            ($user->hasPermission(
                'activities.update.own'
            ) && $activity->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the activity.
     *
     * @param User $user
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $user->hasPermission('activities.delete.any') ||
        ($user->hasPermission('activities.delete.own') &&
            $activity->created_by === $user->id);
    }
}
