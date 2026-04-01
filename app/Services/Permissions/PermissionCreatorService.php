<?php

namespace App\Services\Permissions;

use App\Http\Requests\StorePermissionRequest;
use App\Models\Permission;

/**
 * Handles the creation of new Permission records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Permission.
 */
class PermissionCreatorService
{
    /**
     * Create a new permission from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StorePermissionRequest $request Validated request containing
     * permission data.
     *
     * @return Permission The newly created permission record.
     */
    public function create(StorePermissionRequest $request): Permission
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Permission::create($data);
    }
}
