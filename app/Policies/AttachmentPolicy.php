<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttachmentPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        if ($user->hasRole(Role::ROLE_SUPER_ADMIN)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attachments.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('attachments.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attachments.create');
    }

    public function update(User $user, Attachment $attachment): bool
    {
        return $user->hasPermission('attachments.update.any') ||
            ($user->hasPermission(
                'attachments.update.own'
            ) && $attachment->uploaded_by === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('attachments.delete');
    }

    public function upload(User $user): bool
    {
        return $user->hasPermission('attachments.upload');
    }
}
