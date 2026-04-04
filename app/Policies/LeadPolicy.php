<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the Lead model.
 *
 * This policy controls access to Lead models based on user permissions.
 * It supports both global ("any") and ownership-based ("own") permissions
 * for viewing, creating, updating, deleting, and restoring models.
 *
 * Super admin users are granted all abilities automatically via the
 * before() method.
 */
class LeadPolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other
     * permissions.
     *
     * @param  User $user
     *
     * @return bool|null Return true to allow, null to continue checking
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole(Role::ROLE_SUPER_ADMIN)) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'leads.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User $user
     * @param  Lead $lead
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
     * Determine whether the user can create models.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'leads.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  Lead $lead
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
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  Lead $lead
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
     * Determine wheter the user can restore the model.
     *
     * @param  User $user
     * @param  Lead $lead
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
