<?php

namespace App\Traits;

use App\Models\User;

trait HandlesPolicyPermissions
{
    protected function has(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    protected function owns(User $user, $model, string $permission): bool
    {
        return $this->has($user, $permission) &&
            $model->created_by === $user->id;
    }

    protected function anyOrOwn(
        User $user,
        $model,
        string $any,
        string $own
    ): bool {
        return $this->has($user, $any) || $this->owns($user, $model, $own);
    }
}
