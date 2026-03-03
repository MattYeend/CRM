<?php

namespace App\Policies;

use App\Models\PipelineStage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipelineStagePolicy
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
        return $user->hasPermission('pipeline_stages.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('pipeline_stages.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('pipeline_stages.create');
    }

    public function update(User $user, PipelineStage $pipelineStage): bool
    {
        return $user->hasPermission('pipeline_stages.update.any') ||
            ($user->hasPermission(
                'pipeline_stages.update.own'
            ) && $pipelineStage->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('pipeline_stages.delete');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('pipeline_stages.manage');
    }

    public function assign(User $user): bool
    {
        return $user->hasPermission('pipeline_stages.assign');
    }
}
