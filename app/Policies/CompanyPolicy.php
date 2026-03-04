<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
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
     * Determine whether the user can view any companies.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('companies.view');
    }

    /**
     * Determine whether the user can view the company.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermission('companies.view');
    }

    /**
     * Determine whether the user can create companies.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('companies.create');
    }

    /**
     * Determine whether the user can update the company.
     *
     * @param User $user
     *
     * @param Company $company
     *
     * @return bool
     */
    public function update(User $user, Company $company): bool
    {
        return $user->hasPermission('companies.update.any') ||
            ($user->hasPermission(
                'companies.update.own'
            ) && $company->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the company.
     *
     * @param User $user
     *
     * @param Company $company
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->hasPermission('companies.delete');
    }
}
