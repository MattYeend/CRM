<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;

class EmailVerificationNotificationLogService
{
    public function __construct()
    {
        // Empty
    }

    /**
     * Log verifying in an existing user.
     */
    public function verify(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
        ];

        Log::log(
            Log::ACTION_VERIFY_USER,
            $data,
            $userId,
        );

        return $data;
    }
}
