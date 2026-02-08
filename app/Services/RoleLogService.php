<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Role;
use App\Models\User;

class RoleLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Role.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Role $role The role was created.
     *
     * @return Log The created log entry.
     */
    public function roleCreated(
        User $user,
        int $userId,
        Role $role
    ): array {
        $data = $this->baseRoleData($role) + [
            'created_at' => $role->created_at,
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ROLE_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a Role.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Role $role The role was updated.
     *
     * @return Log The created log entry.
     */
    public function roleUpdated(
        User $user,
        int $userId,
        Role $role
    ): array {
        $data = $this->baseRoleData($role) + [
            'updated_at' => $role->updated_at,
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ROLE_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a Role.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Role $role The role was deleted.
     *
     * @return Log The created log entry.
     */
    public function roleDeleted(
        User $user,
        int $userId,
        Role $role
    ): array {
        $data = $this->baseRoleData($role) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ROLE_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a Role.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Role $role The role was restored.
     *
     * @return Log The created log entry.
     */
    public function roleRestored(
        User $user,
        int $userId,
        Role $role
    ): array {
        $data = $this->baseRoleData($role) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ROLE_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare base data for Role logging.
     *
     * @param Role $role The role being logged.
     *
     * @return array The base data array.
     */
    protected function baseRoleData(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'label' => $role->label,
        ];
    }
}
