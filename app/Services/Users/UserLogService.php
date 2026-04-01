<?php

namespace App\Services\Users;

use App\Models\Log;
use App\Models\User;

/**
 * Handles audit logging for User lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific user action, combining base user data with action-specific
 * timestamp and actor fields. Both the actor and actor ID are nullable to
 * support system-initiated events where no authenticated user is present.
 */
class UserLogService
{
    /**
     * Log a user creation event.
     *
     * @param  User|null $actor The user who performed the action, or null for
     * system-initiated creation.
     * @param  int|null $actorId The ID of the user who performed the action,
     * or null for system-initiated creation.
     * @param  User $user The user that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function userCreated(
        ?User $actor,
        ?int $actorId,
        User $user
    ): array {
        $data = $this->baseUserData($user) + [
            'created_at' => now(),
            'created_by' => $actor?->name,
        ];

        Log::log(
            Log::ACTION_CREATE_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user update event.
     *
     * @param  User|null $actor The user who performed the action, or null for
     * system-initiated updates.
     * @param  int|null $actorId The ID of the user who performed the action,
     * or null for system-initiated updates.
     * @param  User $user The user that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function userUpdated(
        ?User $actor,
        ?int $actorId,
        User $user
    ): array {
        $data = $this->baseUserData($user) + [
            'updated_at' => now(),
            'updated_by' => $actor?->name,
        ];

        Log::log(
            Log::ACTION_UPDATE_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user deletion event.
     *
     * @param  User|null $actor The user who performed the action, or null for
     * system-initiated deletion.
     * @param  int|null $actorId The ID of the user who performed the action,
     * or null for system-initiated deletion.
     * @param  User $user The user that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function userDeleted(
        ?User $actor,
        ?int $actorId,
        User $user
    ): array {
        $data = $this->baseUserData($user) + [
            'deleted_at' => now(),
            'deleted_by' => $actor?->name,
        ];

        Log::log(
            Log::ACTION_DELETE_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user email verification event.
     *
     * @param  User|null $actor The user who performed the action, or null for
     * system-initiated verification.
     * @param  int|null $actorId The ID of the user who performed the action,
     * or null for system-initiated verification.
     * @param  User $user The user that was verified.
     *
     * @return array The structured data written to the log entry.
     */
    public function userVerified(
        ?User $actor,
        ?int $actorId,
        User $user
    ): array {
        $data = $this->baseUserData($user) + [
            'verified_at' => now(),
            'verified_by' => $actor?->name,
        ];

        Log::log(
            Log::ACTION_VERIFY_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user restoration event.
     *
     * @param  User|null $actor The user who performed the action, or null for
     * system-initiated restoration.
     * @param  int|null $actorId The ID of the user who performed the action,
     * or null for system-initiated restoration.
     * @param  User $user The user that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function userRestored(
        ?User $actor,
        ?int $actorId,
        User $user
    ): array {
        $data = $this->baseUserData($user) + [
            'restored_at' => now(),
            'restored_by' => $actor?->name,
        ];

        Log::log(
            Log::ACTION_USER_RESTORED,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all user log entries.
     *
     * Returns a safe array of null values when the user model is not present,
     * supporting system-initiated events where no user record is available.
     *
     * @param  User|null $user The user being logged, or null.
     *
     * @return array The base fields extracted from the user, or null-filled
     * defaults.
     */
    protected function baseUserData(?User $user): array
    {
        if (! $user) {
            return [
                'id' => null,
                'name' => null,
                'email' => null,
                'role' => null,
                'phone' => null,
                'avatar' => null,
            ];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
        ];
    }
}
