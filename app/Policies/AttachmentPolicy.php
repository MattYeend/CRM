<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttachmentPolicy
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
     * Determine whether the user can view any attachments.
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
     * Determine whether the user can view the attachment.
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
     * Determine whether the user can create attachments.
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
     * Determine whether the user can update the attachment.
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
     * Determine whether the user can delete the attachment.
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
     * Determine whether the user can restore the attachment.
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
     * Determine whether the user can upload attachments.
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
