<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the Attachment model.
 *
 * This policy controls access to Attachment models based on user permissions.
 * It supports both global ("any") and ownership-based ("own") permissions
 * for viewing, creating, updating, deleting, and restoring models.
 *
 * Permission checks are delegated to shared helpers that determine whether
 * a user has a given permission, owns the model (via the `created_by` field),
 * or can act on any model versus only those they own.
 *
 * Super admin users are granted all abilities automatically via the
 * before() method.
 */
class AttachmentPolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other
     * permissions.
     *
     * @param User $user
     *
     * @return bool|null Return true to allow, null to continue checking
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
        return $this->has($user, 'attachments.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Attachment $attachment
     *
     * @return bool
     */
    public function view(User $user, Attachment $attachment): bool
    {
        return $this->anyOrOwn(
            $user,
            $attachment,
            'attachments.view.all',
            'attachments.view.own'
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
        return $this->has($user, 'attachments.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Attachment $attachment
     *
     * @return bool
     */
    public function update(User $user, Attachment $attachment): bool
    {
        return $this->anyOrOwn(
            $user,
            $attachment,
            'attachments.update.any',
            'attachments.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Attachment $attachment
     *
     * @return bool
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        return $this->anyOrOwn(
            $user,
            $attachment,
            'attachments.delete.any',
            'attachments.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Attachment $attachment
     *
     * @return bool
     */
    public function restore(User $user, Attachment $attachment): bool
    {
        return $this->anyOrOwn(
            $user,
            $attachment,
            'attachments.restore.any',
            'attachments.restore.own'
        );
    }

    /**
     * Determine whether the user can upload models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function upload(User $user): bool
    {
        return $this->has($user, 'attachments.upload.any');
    }
}
