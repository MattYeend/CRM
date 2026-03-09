<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserUpdater
{
    /**
     * Update the user using request data.
     *
     * @param Request $request
     *
     * @param User $user
     *
     * @return User
     */
    public function update(Request $request, User $user): User
    {
        $data = $request->validated();

        $this->preparePasswordForSave($data);
        $this->handleAvatarForUpdate($request, $user, $data);

        DB::transaction(function () use ($user, $data) {
            $user->update($data);
        });

        return $user->fresh();
    }

    /**
     * Prepare password for saving: hash if provided, remove if empty.
     *
     * @param array $data
     *
     * @return void
     */
    private function preparePasswordForSave(array & $data): void
    {
        if (! array_key_exists('password', $data)) {
            return;
        }

        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
            return;
        }

        unset($data['password']);
    }

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
    private function handleAvatarForUpdate(
        Request $request,
        User $user,
        array & $data
    ): void {
        if (! $request->hasFile('avatar')) {
            return;
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
    }
}
