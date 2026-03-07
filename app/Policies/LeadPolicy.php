<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
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
     * Determine whether the user can view any leads.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'leads.view.all');
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
        return $this->anyOrOwn(
            $user,
            $lead,
            'leads.view.all',
            'leads.view.own'
        );
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
        return $this->has($user, 'leads.create');
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
        return $this->anyOrOwn(
            $user,
            $lead,
            'leads.update.any',
            'leads.update.own'
        );
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
        return $this->anyOrOwn(
            $user,
            $lead,
            'leads.delete.any',
            'leads.delete.own'
        );
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
    public function restore(User $user, Lead $lead): bool
    {
        return $this->anyOrOwn(
            $user,
            $lead,
            'leads.restore.any',
            'leads.restore.own'
        );
    }
}
