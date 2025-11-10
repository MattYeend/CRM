<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;

class RegisteredUserLogService
{
    public function __construct()
    {
        // Empty
    }

    /**
     * Log registering a user.
     */
    public function register(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
        ];

        Log::log(
            Log::ACTION_REGISTER_USER,
            $data,
            $userId,
        );

        return $data;
    }
}
