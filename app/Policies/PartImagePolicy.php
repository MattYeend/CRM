<?php

namespace App\Policies;

use App\Models\PartImage;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartImagePolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Handle all permissions for super admin role.
     *
     * @param User $user
     *
     * @return bool|null
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole(Role::ROLE_SUPER_ADMIN)) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'partImages.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param PartImage $partImage
     *
     * @return bool
     */
    public function view(User $user, PartImage $partImage): bool
    {
        return $this->anyOrOwn(
            $user,
            $partImage,
            'partImages.view.all',
            'partImages.view.own',
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'partImages.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param PartImage $partImage
     *
     * @return bool
     */
    public function update(User $user, PartImage $partImage): bool
    {
        return $this->anyOrOwn(
            $user,
            $partImage,
            'partImages.update.any',
            'partImages.update.own',
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param PartImage $partImage
     *
     * @return bool
     */
    public function delete(User $user, PartImage $partImage): bool
    {
        return $this->anyOrOwn(
            $user,
            $partImage,
            'partImages.delete.any',
            'partImages.delete.own',
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param PartImage $partImage
     *
     * @return bool
     */
    public function restore(User $user, PartImage $partImage): bool
    {
        return $this->anyOrOwn(
            $user,
            $partImage,
            'partImages.restore.any',
            'partImages.restore.own',
        );
    }
}
