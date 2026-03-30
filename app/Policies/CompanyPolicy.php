<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
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
        return $this->has($user, 'companies.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Company $company
     *
     * @return bool
     */
    public function view(User $user, Company $company): bool
    {
        return $this->anyOrOwn(
            $user,
            $company,
            'companies.view.all',
            'companies.view.own'
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
        return $this->has($user, 'companies.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Company $company
     *
     * @return bool
     */
    public function update(User $user, Company $company): bool
    {
        return $this->anyOrOwn(
            $user,
            $company,
            'companies.update.any',
            'companies.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Company $company
     *
     * @return bool
     */
    public function delete(User $user, Company $company): bool
    {
        return $this->anyOrOwn(
            $user,
            $company,
            'companies.delete.any',
            'companies.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Company $company
     *
     * @return bool
     */
    public function restore(User $user, Company $company): bool
    {
        return $this->anyOrOwn(
            $user,
            $company,
            'companies.restore.any',
            'companies.restore.own'
        );
    }
}
