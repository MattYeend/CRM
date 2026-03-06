<?php

namespace App\Policies;

use App\Models\PipelineStage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipelineStagePolicy
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
     * Determine whether the user can view any pipeline stages.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('pipelineStages.view.all');
    }

    /**
     * Determine whether the user can view the pipeline stage.
     *
     * @param User $user
     *
     * @param PipelineStage $pipelineStage
     *
     * @return bool
     */
    public function view(User $user, PipelineStage $pipelineStage): bool
    {
        if ($user->hasPermission('pipelineStages.view.all')) {
            return true;
        }

        return $user->hasPermission('pipelineStages.view.own') &&
            $pipelineStage->created_by === $user->id;
    }

    /**
     * Determine whether the user can create pipeline stages.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('pipelineStages.create');
    }

    /**
     * Determine whether the user can update the pipeline stage.
     *
     * @param User $user
     *
     * @param PipelineStage $pipelineStage
     *
     * @return bool
     */
    public function update(User $user, PipelineStage $pipelineStage): bool
    {
        return $user->hasPermission('pipelineStages.update.any') ||
            ($user->hasPermission(
                'pipelineStages.update.own'
            ) && $pipelineStage->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the pipeline stage.
     *
     * @param User $user
     *
     * @param PipelineStage $pipelineStage
     *
     * @return bool
     */
    public function delete(User $user, PipelineStage $pipelineStage): bool
    {
        return $user->hasPermission('pipelineStages.delete.any') ||
        ($user->hasPermission('pipelineStage.delete.own') &&
            $pipelineStage->created_by === $user->id);
    }

    /**
     * Determine whether the user can manage pipeline stages.
     *
     * @param User $user
     *
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('pipelineStages.manage');
    }

    /**
     * Determine whether the user can assign pipeline stages.
     *
     * @param User $user
     *
     * @return bool
     */
    public function assign(User $user): bool
    {
        return $user->hasPermission('pipelineStages.assign');
    }
}
