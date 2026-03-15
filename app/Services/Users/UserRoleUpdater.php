<?php

namespace App\Services\Users;

use App\Models\User;

class UserRoleUpdater
{
    /**
     * Update role
     *
     * @param User $user
     *
     * @param array $data
     *
     * @return void
     */
    public function update(User $user, array $data): void
    {
        if (! isset($data['role_id'])) {
            return;
        }

        $user->role_id = $data['role_id'];
    }
}
