<?php

namespace App\Traits;

use App\Models\User;

trait HandlesPolicyPermissions
{
    /**
     * Determine if the given user has the specified permission.
     *
     * @param User $user The user to check permissions for.
     * @param string $permission The permission identifier to check.
     *
     * @return bool True if the user has the permission, false otherwise.
     */
    protected function has(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    /**
     * Determine if the given user has the specified permission and owns
     * the model.
     *
     * Ownership is determined by comparing the model's `created_by` field
     * against the user's ID.
     *
     * @param User $user The user to check permissions for.
     * @param mixed $model The model instance to check ownership of.
     * @param string $permission The permission identifier required alongside
     * ownership.
     *
     * @return bool True if the user both has the permission and owns the model,
     * false otherwise.
     */
    protected function owns(User $user, $model, string $permission): bool
    {
        return $this->has($user, $permission) &&
            $model->created_by === $user->id;
    }

    /**
     * Determine if the given user has a broad permission or owns the model
     * with a scoped permission.
     *
     * This is useful for policies where a user can either act on any resource
     * via a global permission (e.g. `post.update-any`), or only on resources
     * they own via a narrower permission (e.g. `post.update-own`).
     *
     * @param User $user The user to check permissions for.
     * @param mixed $model The model instance to check ownership of.
     * @param string $any The permission identifier granting access to any
     * resource.
     * @param string $own The permission identifier granting access to owned
     * resources only.
     *
     * @return bool True if the user has the broad permission or owns the model
     * with the scoped permission.
     */
    protected function anyOrOwn(
        User $user,
        $model,
        string $any,
        string $own
    ): bool {
        return $this->has($user, $any) || $this->owns($user, $model, $own);
    }
}
