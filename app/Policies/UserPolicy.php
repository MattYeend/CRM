<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        return $user->hasPermission('users.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('users.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('users.update.any') ||
            ($user->hasPermission(
                'users.update.own'
            ) && $user->id === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('users.delete');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }
    
    public function assignRoles(User $user): bool
    {
        return $user->hasPermission('users.assign.roles');
    }

    public function assignPermissions(User $user): bool
    {
        return $user->hasPermission('users.assign.permissions');
    }
}
