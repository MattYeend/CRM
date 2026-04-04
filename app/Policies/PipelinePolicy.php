<?php

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the Pipeline model.
 *
 * This policy controls access to Pipeline models based on user permissions.
 * It supports both global ("any") and ownership-based ("own") permissions
 * for viewing, creating, updating, deleting, and restoring models.
 *
 * Permission checks are delegated to shared helpers that determine whether
 * a user has a given permission, owns the model (via the `created_by` field),
 * or can act on any model versus only those they own.
 *
 * Super admin users are granted all abilities automatically via the
 * before() method.
 */
class PipelinePolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other
     * permissions.
     *
     * @param  User $user
     *
     * @return bool|null Return true to allow, null to continue checking
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
     * @param  User $user
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
     * @param  User $user
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
     * @param  User $user
     * @param  Pipeline $pipeline
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
     * @param  User $user
     * @param  Pipeline $pipeline
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
     * @param  User $user
     * @param  Pipeline $pipeline
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
     * Determine whether the user can assign models.
     *
     * @param  User  $user
     *
     * @return  bool
     */
    public function assign(User $user): bool
    {
        return $this->has($user, 'pipelines.assign');
    }
}
