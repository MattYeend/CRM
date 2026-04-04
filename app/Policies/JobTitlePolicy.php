<?php

namespace App\Policies;

use App\Models\JobTitle;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the JobTitle model.
 *
 * This policy controls access to JobTitle models based on user permissions.
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
class JobTitlePolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other
     * permissions.
     *
     * @param  User $user
     *
     * @return  bool|null Return true to allow, null to continue checking
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
        return $this->has($user, 'jobTitles.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User $user
     * @param  JobTitle $jobTitle
     *
     * @return bool
     */
    public function view(User $user, JobTitle $jobTitle): bool
    {
        return $this->anyOrOwn(
            $user,
            $jobTitle,
            'jobTitles.view.all',
            'jobTitles.view.own'
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
        return $this->has($user, 'jobTitles.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  JobTitle $jobTitle
     *
     * @return bool
     */
    public function update(User $user, JobTitle $jobTitle): bool
    {
        if ($jobTitle->users()->exists()) {
            return false;
        }

        return $this->anyOrOwn(
            $user,
            $jobTitle,
            'jobTitles.update.any',
            'jobTitles.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  JobTitle $jobTitle
     *
     * @return bool
     */
    public function delete(User $user, JobTitle $jobTitle): bool
    {
        if ($jobTitle->users()->exists()) {
            return false;
        }

        return $this->anyOrOwn(
            $user,
            $jobTitle,
            'jobTitles.delete.any',
            'jobTitles.delete.own'
        );
    }

    /**
     * Determine wheter the user can restore the model.
     *
     * @param  User $user
     * @param  JobTitle $jobTitle
     *
     * @return bool
     */
    public function restore(User $user, JobTitle $jobTitle): bool
    {
        return $this->anyOrOwn(
            $user,
            $jobTitle,
            'jobTitles.restore.any',
            'jobTitles.restore.own'
        );
    }
}
