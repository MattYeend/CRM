<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
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
        return $user->hasPermission('leads.view');
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
        return $user->hasPermission('leads.update.any') ||
            ($user->hasPermission(
                'leads.update.own'
            ) && $lead->owner_id === $user->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('leads.delete');
    }
}
