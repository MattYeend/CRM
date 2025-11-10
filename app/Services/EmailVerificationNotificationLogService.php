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
            'name' => $user->name,
        ];

        Log::log(
            Log::ACTION_VERIFY_USER,
            $data,
            $userId,
        );

        return $data;
    }
}
