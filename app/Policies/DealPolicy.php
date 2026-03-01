<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DealPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        if ($user->hasPermission('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('deals.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('deals.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('deals.create');
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->hasPermission('deals.update.any') ||
            ($user->hasPermission(
                'deals.update.own'
            ) && $deal->owner_id === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('deals.delete');
    }
}
