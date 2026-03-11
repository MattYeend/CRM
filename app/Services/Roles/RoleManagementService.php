<?php

namespace App\Services\Roles;

use App\Models\Role;

class RoleManagementService
{
    /**
     * Sync permissions for the given role.
     *
     * @param Role $role
     *
     * @param array $data
     *
     * @return void
     */
    public function syncPermissions(Role $role, array $data): void
    {
        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }
    }
}
