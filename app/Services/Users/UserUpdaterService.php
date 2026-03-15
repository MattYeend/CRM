<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Http\Request;

class UserUpdaterService
{
    public function __construct(
        private UserPasswordUpdater $passwordUpdater,
        private UserAvatarUpdater $avatarUpdater,
        private UserRoleUpdater $roleUpdater,
    ) {
        $this->passwordUpdater = $passwordUpdater;
        $this->avatarUpdater = $avatarUpdater;
        $this->roleUpdater = $roleUpdater;
    }
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

        $this->roleUpdater->update($user, $data);
        $this->passwordUpdater->handle($data);
        $this->avatarUpdater->handle($request, $user, $data);

        $data['updated_by'] = auth()->id();
        $data['updated_at'] = now();

        $user->update($data);

        return $user->fresh();
    }
}
