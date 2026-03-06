<?php

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipelinePolicy
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
     * Determine whether the user can view any pipelines.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('pipelines.view.all');
    }

    /**
     * Determine whether the user can view the pipeline.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermission('pipelines.view');
    }

    /**
     * Determine whether the user can create pipelines.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('pipelines.create');
    }

    /**
     * Determine whether the user can update the pipeline.
     *
     * @param User $user
     *
     * @param Pipeline $pipeline
     *
     * @return bool
     */
    public function update(User $user, Pipeline $pipeline): bool
    {
        return $user->hasPermission('pipelines.update.any') ||
            ($user->hasPermission(
                'pipelines.update.own'
            ) && $pipeline->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the pipeline.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->hasPermission('pipelines.delete');
    }

    /**
     * Determine whether the user can manage pipelines.
     *
     * @param User $user
     *
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('pipelines.manage');
    }

    /**
     * Determine whether the user can assign pipelines.
     *
     * @param User $user
     *
     * @return bool
     */
    public function assign(User $user): bool
    {
        return $user->hasPermission('pipelines.assign');
    }
}
