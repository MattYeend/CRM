<?php

namespace App\Services;

use App\Models\Permission;

class PermissionDestructorService
{
    /**
     * Soft-delete a permission.
     *
     * @param Permission $permission
     *
     * @return void
     */
    public function destroy(Permission $permission): void
    {
        $permission->update([
            'deleted_by' => auth()->id(),
        ]);

        $permission->delete();
    }

    /**
     * Restore a trashed permission.
     *
     * @param int $id
     *
     * @return Permission
     */
    public function restore(int $id): Permission
    {
        $permission = Permission::withTrashed()->findOrFail($id);

        if ($permission->trashed()) {
            $permission->restore();
        }

        return $permission;
    }
}
