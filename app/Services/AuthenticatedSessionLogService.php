<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;

class AuthenticatedSessionLogService
{
    public function __construct()
    {
        // Empty
    }

    /**
     * Log MFA enabled for an existing user.
     */
    public function mfaEnabled(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
        ];

        Log::log(
            Log::ACTION_MFA_ENABLED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log logging in an existing user.
     */
    public function login(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
        ];

        Log::log(
            Log::ACTION_LOGIN,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log successful login of an existing user.
     */
    public function loginSuccess(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
        ];

        Log::log(
            Log::ACTION_LOGIN_SUCCESS,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log logging out an existing user.
     */
    public function logout(User $user, int $userId): array
    {
        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
        ];

        Log::log(
            Log::ACTION_LOGOUT,
            $data,
            $userId,
        );

        return $data;
    }
}
