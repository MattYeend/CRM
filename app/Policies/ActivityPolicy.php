<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
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
        return $this->has($user, 'activities.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function view(User $user, Activity $activity): bool
    {
        return $this->anyOrOwn(
            $user,
            $activity,
            'activities.view.all',
            'activities.view.own'
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
        return $this->has($user, 'activities.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function update(User $user, Activity $activity): bool
    {
        return $this->anyOrOwn(
            $user,
            $activity,
            'activities.update.any',
            'activities.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $this->anyOrOwn(
            $user,
            $activity,
            'activities.delete.any',
            'activities.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function restore(User $user, Activity $activity): bool
    {
        return $this->anyOrOwn(
            $user,
            $activity,
            'activities.restore.any',
            'activities.restore.own'
        );
    }
}
