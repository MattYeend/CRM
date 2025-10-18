<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;

class NewPasswordLogService
{
    public function __construct()
    {
        // Empty
    }

    /**
     * Log resetting a user's password.
     */
    public function reset(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
        ];

        Log::log(
            Log::ACTION_RESET_PASSWORD,
            $data,
            $userId,
        );

        return $data;
    }
}
