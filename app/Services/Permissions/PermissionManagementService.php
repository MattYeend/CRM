<?php

namespace App\Services\Permissions;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;

/**
 * Orchestrates permission lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for permission create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class PermissionManagementService
{
    /**
     * Service responsible for creating new permission records.
     *
     * @var PermissionCreatorService
     */
    private PermissionCreatorService $creator;

    /**
     * Service responsible for updating existing permission records.
     *
     * @var PermissionUpdaterService
     */
    private PermissionUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring permission records.
     *
     * @var PermissionDestructorService
     */
    private PermissionDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  PermissionCreatorService $creator Handles permission creation.
     * @param  PermissionUpdaterService $updater Handles permission updates.
     * @param  PermissionDestructorService $destructor Handles permission
     * deletion and restoration.
     */
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
     * @param  StorePermissionRequest $request Validated request containing
     * permission data.
     *
     * @return Permission The newly created permission.
     */
    public function store(StorePermissionRequest $request): Permission
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing permission.
     *
     * @param  UpdatePermissionRequest $request Validated request containing
     * updated permission data.
     * @param  Permission $permission The permission instance to update.
     *
     * @return Permission The updated permission.
     */
    public function update(
        UpdatePermissionRequest $request,
        Permission $permission
    ): Permission {
        return $this->updater->update($request, $permission);
    }

    /**
     * Soft-delete a permission.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Permission $permission The permission to delete.
     *
     * @return void
     */
    public function destroy(Permission $permission): void
    {
        $this->destructor->destroy($permission);
    }

    /**
     * Restore a soft-deleted permission.
     *
     * @param  int $id The primary key of the soft-deleted permission.
     *
     * @return Permission The restored permission.
     */
    public function restore(int $id): Permission
    {
        return $this->destructor->restore($id);
    }
}
