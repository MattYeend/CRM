<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserManagementService
{
    private UserCreator $creator;
    private UserUpdater $updater;
    private UserRolesManager $rolesManager;
    private UserDestructor $destructor;

    public function __construct(
        UserCreator $creator,
        UserUpdater $updater,
        UserRolesManager $rolesManager,
        UserDestructor $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->rolesManager = $rolesManager;
        $this->destructor = $destructor;
    }

    /**
     * Create a new user.
     *
     * @param Request $request
     *
     * @return User
     */
    public function store(Request $request): User
    {
        $user = $this->creator->create($request);

        // roles come from the validated payload; re-validate via request object
        $data = $request->all();
        $this->rolesManager->syncIfProvided($user, $data);

        return $user->load('roles');
    }

    /**
     * Update an existing user.
     *
     * @param Request $request
     *
     * @param User $user
     *
     * @return User
     */
    public function update(Request $request, User $user): User
    {
        $user = $this->updater->update($request, $user);

        // sync roles if present
        $data = $request->all();
        if (array_key_exists('roles', $data)) {
            $user->roles()->sync($data['roles'] ?? []);
        }

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

    /**
     * Permanently delete a user.
     *
     * @param int $id
     *
     * @return void
     */
    public function forceDelete(int $id): void
    {
        $this->destructor->forceDelete($id);
    }

    /**
     * Attach roles to a user without detaching existing ones.
     *
     * @param Request $request
     *
     * @param User $user
     *
     * @return User
     */
    public function attachRoles(Request $request, User $user): User
    {
        return $this->rolesManager->attach($request, $user)->load('roles');
    }

    /**
     * Detach roles from a user.
     *
     * @param Request $request
     *
     * @param User $user
     *
     * @return User
     */
    public function detachRoles(Request $request, User $user): User
    {
        return $this->rolesManager->detach($request, $user)->load('roles');
    }
}
