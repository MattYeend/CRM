<?php

namespace App\Services\Roles;

use App\Models\Role;

/**
 * Handles role-related management operations.
 *
 * Responsible for coordinating updates to role relationships,
 * such as syncing permissions.
 */
class RoleManagementService
{
    /**
     * Sync permissions for the given role.
     *
     * If the 'permissions' key exists in the provided data,
     * the role's permissions will be synchronized accordingly.
     *
     * @param  Role  $role The role being updated.
     * @param  array $data The data containing permission IDs.
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
