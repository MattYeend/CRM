<?php

namespace App\Services\Permissions;

use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;

class PermissionUpdaterService
{
    /**
     * Update the permission using request data.
     *
     * @param UpdatePermissionRequest $request
     *
     * @param Permission $permission
     *
     * @return Permission
     */
    public function update(
        UpdatePermissionRequest $request,
        Permission $permission
    ): Permission {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $permission->update($data);

        return $permission->fresh();
    }
}
