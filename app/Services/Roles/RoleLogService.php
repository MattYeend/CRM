<?php

namespace App\Services\Roles;

use App\Models\Log;
use App\Models\Role;
use App\Models\User;

/**
 * Handles audit logging for Role lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific role action, combining base role data with
 * action-specific timestamp and actor fields.
 */
class RoleLogService
{
    /**
     * Log a role creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Role $role The role that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function roleCreated(
        User $user,
        int $userId,
        Role $role
    ): array {
        $data = $this->baseRoleData($role) + [
            'created_at' => now(),
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
     * Log a role update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Role $role The role that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function roleUpdated(
        User $user,
        int $userId,
        Role $role
    ): array {
        $data = $this->baseRoleData($role) + [
            'updated_at' => now(),
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
     * Log a role deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Role $role The role that was deleted.
     *
     * @return array The structured data written to the log entry.
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
     * Log a role restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Role $role The role that was restored.
     *
     * @return array The structured data written to the log entry.
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
     * Build the base data array shared across all role log entries.
     *
     * @param  Role $role The role being logged.
     *
     * @return array The base fields extracted from the role.
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
