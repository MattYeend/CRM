<?php

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipelinePolicy
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
        return $this->has($user, 'pipelines.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Pipeline $pipeline
     *
     * @return bool
     */
    public function view(User $user, Pipeline $pipeline): bool
    {
        return $this->anyOrOwn(
            $user,
            $pipeline,
            'pipelines.view.all',
            'pipelines.view.own'
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
        return $this->has($user, 'pipelines.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Pipeline $pipeline
     *
     * @return bool
     */
    public function update(User $user, Pipeline $pipeline): bool
    {
        return $this->anyOrOwn(
            $user,
            $pipeline,
            'pipelines.update.any',
            'pipelines.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Pipeline $pipeline
     *
     * @return bool
     */
    public function delete(User $user, Pipeline $pipeline): bool
    {
        return $this->anyOrOwn(
            $user,
            $pipeline,
            'pipelines.delete.any',
            'pipelines.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Pipeline $pipeline
     *
     * @return bool
     */
    public function restore(User $user, Pipeline $pipeline): bool
    {
        return $this->anyOrOwn(
            $user,
            $pipeline,
            'pipelines.restore.any',
            'pipelines.restore.own'
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
        return $this->has($user, 'pipelines.manage');
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
        return $this->has($user, 'pipelines.assign');
    }
}
