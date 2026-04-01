<?php

namespace App\Services\Permissions;

use App\Models\Permission;

/**
 * Handles soft deletion and restoration of Permission records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class PermissionDestructorService
{
    /**
     * Soft-delete a permission.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the permission.
     *
     * @param  Permission $permission The permission instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Permission $permission): void
    {
        $userId = auth()->id();

        $permission->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);
        $permission->roles()->detach();

        $permission->delete();
    }

    /**
     * Restore a soft-deleted permission.
     *
     * Looks up the permission including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the permission. Returns the permission unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted permission.
     *
     * @return Permission The restored permission instance.
     */
    public function restore(int $id): Permission
    {
        $userId = auth()->id();

        $permission = Permission::withTrashed()->findOrFail($id);

        if ($permission->trashed()) {
            $permission->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $permission->restore();
        }

        return $permission;
    }
}
