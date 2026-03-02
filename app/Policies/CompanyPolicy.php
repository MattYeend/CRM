<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
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
        return $user->hasPermission('companies.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('companies.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('companies.create');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->hasPermission('companies.update.any') ||
            ($user->hasPermission(
                'companies.update.own'
            ) && $company->created_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('companies.delete');
    }
}
