<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
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
     * Determine whether the user can view any leads.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('leads.view.all');
    }

    /**
     * Determine whether the user can view the lead.
     *
     * @param User $user
     *
     * @param Lead $lead
     *
     * @return bool
     */
    public function view(User $user, Lead $lead): bool
    {
        if ($user->hasPermission('leads.view.all')) {
            return true;
        }

        return $user->hasPermission('leads.view.own') &&
            $lead->created_by === $user->id;
    }

    /**
     * Determine whether the user can create leads.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('leads.create');
    }

    /**
     * Determine whether the user can update the lead.
     *
     * @param User $user
     *
     * @param Lead $lead
     *
     * @return bool
     */
    public function update(User $user, Lead $lead): bool
    {
        return $user->hasPermission('leads.update.any') ||
            ($user->hasPermission(
                'leads.update.own'
            ) && $lead->owner_id === $user->id);
    }

    /**
     * Determine whether the user can delete the lead.
     *
     * @param User $user
     *
     * @param Lead $lead
     *
     * @return bool
     */
    public function delete(User $user, Lead $lead): bool
    {
        return $user->hasPermission('leads.delete.any') ||
        ($user->hasPermission('leads.delete.own') &&
            $lead->created_by === $user->id);
    }

    /**
     * Determine wheter the user can restore the lead
     *
     * @param User $user
     *
     * @param Lead $lead
     *
     * @return bool
     */
    public function restore(User $user, Lead $lead)
    {
        return $user->hasPermission('leads.restore.any') ||
        ($user->hasPermission('leads.restore.own') &&
            $lead->created_by === $user->id);
    }
}
