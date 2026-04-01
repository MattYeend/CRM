<?php

namespace App\Services\Permissions;

use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;

/**
 * Handles updates to Permission records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the permission.
 */
class PermissionUpdaterService
{
    /**
     * Update an existing permission.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the permission, and returns
     * a fresh instance.
     *
     * @param  UpdatePermissionRequest $request The request containing
     * validated permission data.
     * @param  Permission $permission The permission to update.
     *
     * @return Permission The updated permission instance.
     */
    public function update(
        UpdatePermissionRequest $request,
        Permission $permission
    ): Permission {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $permission->update($data);

        return $permission->fresh();
    }
}
