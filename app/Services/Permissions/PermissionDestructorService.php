<?php

namespace App\Services\Permissions;

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
        $userId = auth()->id();

        $permission->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);
        $permission->roles()->detach();

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
