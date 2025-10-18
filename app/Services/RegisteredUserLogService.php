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
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
        ];

        Log::log(
            Log::ACTION_REGISTER_USER,
            $data,
            $userId,
        );

        return $data;
    }
}
