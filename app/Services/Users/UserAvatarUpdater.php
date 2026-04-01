<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Handles avatar file replacement during user updates.
 *
 * When a new avatar file is present in the request, deletes the user's
 * existing avatar from the public disk and stores the new file in its place,
 * writing the new path back into the data array.
 */
class UserAvatarUpdater
{
    /**
     * Handle avatar upload and deletion on update.
     *
     * If no avatar file is present in the request the method returns early
     * without modifying the data array. Otherwise, any existing avatar is
     * deleted from the public disk before the new file is stored and its
     * path written back into the data array.
     *
     * @param  Request|UpdateUserRequest $request Incoming HTTP request;
     * checked for an avatar file upload.
     * @param  User $user The user whose avatar may need to be replaced.
     * @param  array $data The validated data array, passed by reference so
     * the avatar path can be injected directly.
     *
     * @return void
     */
    public function handle(Request $request, User $user, array &$data): void
    {
        if (! $request->hasFile('avatar')) {
            return;
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
    }
}
