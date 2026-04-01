<?php

namespace App\Services\Users;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Handles updates to User records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the user.
 */
class UserUpdaterService
{
    /**
     * Service responsible for handling password updates, including hashing and
     * validation of password fields.
     *
     * @var UserPasswordUpdater
     */
    private UserPasswordUpdater $passwordUpdater;

    /**
     * Service responsible for handling avatar file uploads and deletions
     * during user updates.
     *
     * @var UserAvatarUpdater
     */
    private UserAvatarUpdater $avatarUpdater;

    /**
     * Service responsible for handling role assignment and updates during
     * user updates.
     *
     * @var UserRoleUpdater
     */
    private UserRoleUpdater $roleUpdater;

    /**
     * Inject the required services into the updater service.
     *
     * @param  UserPasswordUpdater $passwordUpdater Handles password updates.
     * @param  UserAvatarUpdater $avatarUpdater Handles avatar file updates.
     * @param  UserRoleUpdater $roleUpdater Handles role assignment updates.
     */
    public function __construct(
        UserPasswordUpdater $passwordUpdater,
        UserAvatarUpdater $avatarUpdater,
        UserRoleUpdater $roleUpdater,
    ) {
        $this->passwordUpdater = $passwordUpdater;
        $this->avatarUpdater = $avatarUpdater;
        $this->roleUpdater = $roleUpdater;
    }
    
    /**
     * Update an existing user.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the user, and returns
     * a fresh instance.
     *
     * @param  UpdateUserRequest $request The request containing validated user data.
     * @param  User $user The user to update.
     *
     * @return User The updated user instance.
     */
    public function update(UpdateUserRequest $request, User $user): User
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
