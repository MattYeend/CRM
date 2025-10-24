<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserDestructor
{
    /**
     * Soft-delete a user.
     *
     * @param User $user
     *
     * @return void
     */
    public function destroy(User $user): void
    {
        $user->delete();
    }

    /**
     * Restore a trashed user.
     *
     * @param int $id
     *
     * @return User
     */
    public function restore(int $id): User
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            $user->restore();
        }

        return $user;
    }

    /**
     * Force-delete a user and remove avatar if present.
     *
     * @param int $id
     *
     * @return void
     */
    public function forceDelete(int $id): void
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->forceDelete();
    }
}
