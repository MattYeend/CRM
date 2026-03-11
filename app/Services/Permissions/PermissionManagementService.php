<?php

namespace App\Services\Permissions;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;

class PermissionManagementService
{
    private PermissionCreatorService $creator;
    private PermissionUpdaterService $updater;
    private PermissionDestructorService $destructor;

    public function __construct(
        PermissionCreatorService $creator,
        PermissionUpdaterService $updater,
        PermissionDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new permission.
     *
     * @param StorePermissionRequest $request
     *
     * @return Permission
     */
    public function store(StorePermissionRequest $request): Permission
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing permission.
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
        return $this->updater->update($request, $permission);
    }

    /**
     * Delete a permission (soft delete).
     *
     * @param Permission $permission
     *
     * @return void
     */
    public function destroy(Permission $permission): void
    {
        $this->destructor->destroy($permission);
    }

    /**
     * Restore a soft-deleted permission
     *
     * @param int $id
     *
     * @return Permission
     */
    public function restore(int $id): Permission
    {
        return $this->destructor->restore($id);
    }
}
