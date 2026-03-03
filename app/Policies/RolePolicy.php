<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
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
        return $user->hasPermission('roles.view');
    }
    
    public function view(User $user): bool
    {
        return $user->hasPermission('roles.view');
    }

}
