<?php

namespace App\Services\Users;

use Illuminate\Support\Facades\Hash;

/**
 * Handles password hashing during user create and update operations.
 *
 * When a password key is present in the data array and carries a non-empty
 * value, it is replaced with its bcrypt hash. When the key is present but
 * empty, it is removed so the existing stored password is preserved.
 */
class UserPasswordUpdater
{
    /**
     * Prepare the password field for persistence.
     *
     * Returns early without modification if no password key exists in the
     * data array. If the key is present but empty, it is unset to prevent
     * overwriting the existing password. If a value is present, it is hashed
     * in place.
     *
     * @param  array $data The validated data array, passed by reference so
     * the password can be hashed or removed directly.
     *
     * @return void
     */
    public function handle(array &$data): void
    {
        if (! array_key_exists('password', $data)) {
            return;
        }

        if (! $data['password']) {
            unset($data['password']);
            return;
        }

        $data['password'] = Hash::make($data['password']);
    }
}
