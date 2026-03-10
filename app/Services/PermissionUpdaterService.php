<?php

namespace App\Services;

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
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $permission->update($data);

        return $permission->fresh();
    }
}
