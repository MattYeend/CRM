<?php

namespace App\Services\Users;

use App\Models\User;

/**
 * Handles role assignment during user create and update operations.
 *
 * When a role_id is present in the data array, it is written to the user
 * model's role_id attribute. If no role_id is provided the method returns
 * early without modifying the user.
 */
class UserRoleUpdater
{
    /**
     * Assign the role to the user if a role_id is present in the data array.
     *
     * Returns early without modification if role_id is not set. Otherwise
     * writes the role_id directly to the user model's attribute.
     *
     * @param  User $user The user whose role may be updated.
     * @param  array $data The validated data array, checked for a role_id
     * value.
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
