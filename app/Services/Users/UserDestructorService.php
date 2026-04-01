<?php

namespace App\Services\Users;

use App\Models\User;

/**
 * Handles the soft-deletion and restoration of User records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class UserDestructorService
{
    /**
     * Soft-delete a user.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the user.
     *
     * @param  User $user The user instance to soft-delete.
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
     * Restore a soft-deleted user.
     *
     * Looks up the user including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the user. Returns the user unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted user.
     *
     * @return User The restored user instance.
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
