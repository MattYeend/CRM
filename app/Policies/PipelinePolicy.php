<?php

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipelinePolicy
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
        return $user->hasPermission('pipelines.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('pipelines.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('pipelines.create');
    }

    public function update(User $user, Pipeline $pipeline): bool
    {
        return $user->hasPermission('pipelines.update.any') ||
            ($user->hasPermission(
                'pipelines.update.own'
            ) && $pipeline->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('pipelines.delete');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('pipelines.manage');
    }

    public function assign(User $user): bool
    {
        return $user->hasPermission('pipelines.assign');
    }
}
