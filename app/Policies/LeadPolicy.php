<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
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
        return $user->hasPermission('leads.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($user->hasPermission('leads.update.any')) {
            return true;
        }

        if (
            $user->hasPermission('leads.update.own') &&
            $lead->owner_id === $user->id
        ) {
            return true;
        }

        return false;
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('leads.delete');
    }
}
