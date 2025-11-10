<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;

class ProfileLogService
{
    public function __construct()
    {
        // Empty
    }

    /**
     * Log updating an existing user profile.
     */
    public function update(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'updated_at' => $user->updated_at,
        ];

        Log::log(
            Log::ACTION_PROFILE_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log deleting user profile.
     */
    public function delete(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];

        Log::log(
            Log::ACTION_PROFILE_DELETED,
            $data,
            $userId,
        );

        return $data;
    }
}
