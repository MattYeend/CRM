<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;

class PasswordLogService
{
    public function __construct()
    {
        // Empty
    }

    /**
     * Log updating an existing users password.
     */
    public function update(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
        ];

        Log::log(
            Log::ACTION_PASSWORD_CHANGED,
            $data,
            $userId,
        );

        return $data;
    }
}
