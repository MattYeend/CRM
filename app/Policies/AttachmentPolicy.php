<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttachmentPolicy
{
    use HandlesAuthorization;

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
        return $user->hasPermission('attachments.view');
    }

    /**
     * Determine whether the user can view the attachment.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermission('attachments.view');
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
        return $user->hasPermission('attachments.create');
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
        return $user->hasPermission('attachments.update.any') ||
            ($user->hasPermission(
                'attachments.update.own'
            ) && $attachment->uploaded_by === $user->id);
    }

    /**
     * Determine whether the user can delete the attachment.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->hasPermission('attachments.delete');
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
        return $user->hasPermission('attachments.upload');
    }
}
