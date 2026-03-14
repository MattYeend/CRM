<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserDestructorService
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
        $authId = auth()->id();

        $user->update([
            'deleted_by' => $authId,
            'deleted_at' => now(),
        ]);

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
        $authId = auth()->id();

        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            $user->update([
                'restored_by' => $authId,
                'restored_at' => now(),
            ]);
            $user->restore();
        }

        return $user;
    }
}
