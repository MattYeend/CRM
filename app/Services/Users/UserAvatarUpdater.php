<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserAvatarUpdater
{
    /**
     * Handle avatar upload and deletion on update.
     *
     * @param Request $request
     *
     * @param User $user
     *
     * @param array $data
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
