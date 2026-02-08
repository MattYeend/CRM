<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Permission;
use App\Models\User;

class PermissionLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Permission.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Permission $permission The permission was created.
     *
     * @return Log The created log entry.
     */
    public function permissionCreated(
        User $user,
        int $userId,
        Permission $permission
    ): array {
        $data = $this->basePermissionData($permission) + [
            'created_at' => $permission->created_at,
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
     * Log the update of a Permission.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Permission $permission The permission was updated.
     *
     * @return Log The created log entry.
     */
    public function permissionUpdated(
        User $user,
        int $userId,
        Permission $permission
    ): array {
        $data = $this->basePermissionData($permission) + [
            'updated_at' => $permission->updated_at,
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
     * Log the deletion of a Permission.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Permission $permission The permission was deleted.
     *
     * @return Log The created log entry.
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
     * Log the restoration of a Permission.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Permission $permission The permission was restored.
     *
     * @return Log The created log entry.
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
     * Prepare the base data for logging a Permission.
     *
     * @param Permission $permission The permission being logged.
     *
     * @return array The base data array.
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
