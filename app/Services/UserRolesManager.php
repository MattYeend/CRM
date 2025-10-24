<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserRolesManager
{
    /**
     * Sync roles if present (used after create/update).
     *
     * @param User $user
     *
     * @param array $data
     *
     * @return void
     */
    public function syncIfProvided(User $user, array $data): void
    {
        if (! array_key_exists('roles', $data)) {
            return;
        }

        if (! is_array($data['roles']) || count($data['roles']) === 0) {
            return;
        }

        $user->roles()->sync($data['roles']);
    }

    /**
     * Attach roles without detaching existing ones.
     *
     * @param Request $request
     *
     * @param User $user
     *
     * @return User
     */
    public function attach(Request $request, User $user): User
    {
        $data = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        $user->roles()->syncWithoutDetaching($data['roles']);

        return $user;
    }

    /**
     * Detach roles.
     *
     * @param Request $request
     *
     * @param User $user
     *
     * @return User
     */
    public function detach(Request $request, User $user): User
    {
        $data = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        $user->roles()->detach($data['roles']);

        return $user;
    }
}
