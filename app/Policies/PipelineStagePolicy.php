<?php

namespace App\Policies;

use App\Models\PipelineStage;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipelineStagePolicy
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
        return $this->has($user, 'pipelineStages.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param PipelineStage $pipelineStage
     *
     * @return bool
     */
    public function view(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->anyOrOwn(
            $user,
            $pipelineStage,
            'pipelineStages.view.all',
            'pipelineStages.view.own'
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
        return $this->has($user, 'pipelineStages.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param PipelineStage $pipelineStage
     *
     * @return bool
     */
    public function update(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->anyOrOwn(
            $user,
            $pipelineStage,
            'pipelineStages.update.any',
            'pipelineStages.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param PipelineStage $pipelineStage
     *
     * @return bool
     */
    public function delete(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->anyOrOwn(
            $user,
            $pipelineStage,
            'pipelineStages.delete.any',
            'pipelineStages.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param PipelineStage $pipelineStage
     *
     * @return bool
     */
    public function restore(User $user, PipelineStage $pipelineStage): bool
    {
        return $this->anyOrOwn(
            $user,
            $pipelineStage,
            'pipelineStages.restore.any',
            'pipelineStages.restore.own'
        );
    }

    /**
     * Determine whether the user can manage models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $this->has($user, 'pipelineStages.manage');
    }

    /**
     * Determine whether the user can assign models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function assign(User $user): bool
    {
        return $this->has($user, 'pipelineStages.assign');
    }
}
