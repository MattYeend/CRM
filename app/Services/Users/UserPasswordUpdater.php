<?php

namespace App\Services\Users;

use Illuminate\Support\Facades\Hash;

class UserPasswordUpdater
{
    /**
     * Prepare password for saving: hash if provided, remove if empty.
     *
     * @param array $data
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
