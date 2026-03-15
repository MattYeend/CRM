<?php

namespace App\Services\Users;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserManagementService
{
    private UserCreatorService $creator;
    private UserUpdaterService $updater;
    private UserDestructorService $destructor;

    public function __construct(
        UserCreatorService $creator,
        UserUpdaterService $updater,
        UserDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new user.
     *
     * @param StoreUserRequest $request
     *
     * @return User
     */
    public function store(StoreUserRequest $request): User
    {
        $user = $this->creator->create($request);

        return $user->load('roles');
    }

    /**
     * Update an existing user.
     *
     * @param UpdateUserRequest $request
     *
     * @param User $user
     *
     * @return User
     */
    public function update(UpdateUserRequest $request, User $user): User
    {
        $user = $this->updater->update($request, $user);

        return $user->load('roles');
    }

    /**
     * Delete a user (soft delete).
     *
     * @param User $user
     *
     * @return void
     */
    public function destroy(User $user): void
    {
        $this->destructor->destroy($user);
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param int $id
     *
     * @return User
     */
    public function restore(int $id): User
    {
        return $this->destructor->restore($id);
    }
}
