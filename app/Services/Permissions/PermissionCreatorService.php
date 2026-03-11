<?php

namespace App\Services\Permissions;

use App\Http\Requests\StorePermissionRequest;
use App\Models\Permission;

class PermissionCreatorService
{
    /**
     * Create a new permission from request data.
     *
     * @param StorePermissionRequest $request
     *
     * @return Permission
     */
    public function create(StorePermissionRequest $request): Permission
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Permission::create($data);
    }
}
