<?php

namespace App\Services\Permissions;

use App\Models\Log;
use App\Models\Permission;
use App\Models\User;

/**
 * Handles audit logging for Permission lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific permission action, combining base permission data with
 * action-specific timestamp and actor fields.
 */
class PermissionLogService
{
    /**
     * Log a permission creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Permission $permission The permission that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function permissionCreated(
        User $user,
        int $userId,
        Permission $permission
    ): array {
        $data = $this->basePermissionData($permission) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PERMISSION_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a permission update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Permission $permission The permission that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function permissionUpdated(
        User $user,
        int $userId,
        Permission $permission
    ): array {
        $data = $this->basePermissionData($permission) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PERMISSION_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a permission deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Permission $permission The permission that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function permissionDeleted(
        User $user,
        int $userId,
        Permission $permission
    ): array {
        $data = $this->basePermissionData($permission) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PERMISSION_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a permission restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Permission $permission The permission that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function permissionRestored(
        User $user,
        int $userId,
        Permission $permission
    ): array {
        $data = $this->basePermissionData($permission) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PERMISSION_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all permission log entries.
     *
     * @param  Permission $permission The permission being logged.
     *
     * @return array The base fields extracted from the permission.
     */
    protected function basePermissionData(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'label' => $permission->label,
        ];
    }
}
