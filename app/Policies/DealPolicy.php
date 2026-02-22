<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        // Empty constructor for the time being
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
        if ($user->hasPermission('deals.update.any')) {
            return true;
        }

        if (
            $user->hasPermission('deals.update.own') &&
            $deal->owner_id === $user->id
        ) {
            return true;
        }

        return false;
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('deals.delete');
    }
}
