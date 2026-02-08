<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;

class UserLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a User.
     *
     * @param User|null $actor The user that created the user (nullable).
     *
     * @param int|null $actorId The ID of the user who performed the
     * action (nullable).
     *
     * @param User $user The user being logged.
     *
     * @return array The created log payload.
     */
    public function userCreated(
        ?User $actor,
        ?int $actorId,
        User $user
    ): array {
        $data = $this->baseUserData($user) + [
            'created_at' => $user->created_at,
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
     * Log the update of a User.
     *
     * @param User|null $actor The user that updated the user (nullable).
     *
     * @param int|null $actorId The ID of the user who performed the
     * action (nullable).
     *
     * @param User $user The user being logged.
     *
     * @return array The updated log payload.
     */
    public function userUpdated(
        ?User $actor,
        ?int $actorId,
        User $user
    ): array {
        $data = $this->baseUserData($user) + [
            'updated_at' => $user->updated_at,
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
     * Log the deletion of a User.
     *
     * @param User|null $actor The user that deleted the user (nullable).
     *
     * @param int|null $actorId The ID of the user who performed the
     * action (nullable).
     *
     * @param User $user The user being logged.
     *
     * @return array The deleted log payload.
     */
    public function userDeleted(
        ?User $actor,
        ?int $actorId,
        User $user
    ): array {
        $data = $this->baseUserData($user) + [
            'deleted_at' => $user->deleted_at,
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
     * Log the verification of a User.
     *
     * @param User|null $actor The user that verified the user (nullable).
     *
     * @param int|null $actorId The ID of the user who performed the
     * action (nullable).
     *
     * @param User $user The user being logged.
     *
     * @return array The verified log payload.
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
     * Log the restoration of a User.
     *
     * @param User|null $actor The user that restored the user (nullable).
     *
     * @param int|null $actorId The ID of the user who performed the
     * action (nullable).
     *
     * @param User $user The user being logged.
     *
     * @return array The restored log payload.
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
     * Log the force deletion of a User.
     *
     * @param User|null $actor The user that force deleted the user (nullable).
     *
     * @param int|null $actorId The ID of the user who performed the
     * action (nullable).
     *
     * @param User|null $user The user being logged.
     *
     * @return array The force deleted log payload.
     */
    public function userForceDeleted(
        ?User $actor,
        ?int $actorId,
        ?User $user
    ): array {
        $data = $this->baseUserData($user) + [
            'force_deleted_at' => now(),
            'force_deleted_by' => $actor?->name,
        ];

        Log::log(
            Log::ACTION_USER_FORCED_DELETED,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Prepare base user data for logging.
     *
     * Accepts nullable User and returns a safe array even when the
     * model is missing.
     *
     * @param User|null $user
     *
     * @return array
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
