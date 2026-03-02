<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
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
        return $user->hasPermission('activities.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('activities.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('activities.create');
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->hasPermission('activities.update.any') ||
            ($user->hasPermission(
                'activities.update.own'
            ) && $activity->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('activities.delete');
    }
}
