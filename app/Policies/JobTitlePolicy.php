<?php

namespace App\Policies;

use App\Models\JobTitle;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobTitlePolicy
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
     * Determine whether the user can view any job titles.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'jobTitles.view.all');
    }

    /**
     * Determine whether the user can view the job title.
     *
     * @param User $user
     *
     * @param JobTitle $jobTitle
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
     * Determine whether the user can create job titles.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'jobTitles.create');
    }

    /**
     * Determine whether the user can update the job title.
     *
     * @param User $user
     *
     * @param JobTitle $jobTitle
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
     * Determine whether the user can delete the job title.
     *
     * @param User $user
     *
     * @param JobTitle $jobTitle
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
     * Determine wheter the user can restore the job title
     *
     * @param User $user
     *
     * @param JobTitle $jobTitle
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
