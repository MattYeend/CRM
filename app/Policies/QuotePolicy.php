<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for the Quote model.
 *
 * This policy controls access to Quote models based on user permissions.
 * It supports both global ("any") and ownership-based ("own") permissions
 * for viewing, creating, updating, deleting, and restoring models.
 *
 * Permission checks are delegated to shared helpers that determine whether
 * a user has a given permission, owns the model (via the `created_by` field),
 * or can act on any model versus only those they own.
 *
 * Super admin users are granted all abilities automatically via the
 * before() method.
 */
class QuotePolicy
{
    use HandlesAuthorization, HandlesPolicyPermissions;

    /**
     * Grant all abilities to super admin users before checking other permissions.
     *
     * @param User $user
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
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'quotes.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @param Quote $quote
     *
     * @return bool
     */
    public function view(User $user, Quote $quote): bool
    {
        return $this->anyOrOwn(
            $user,
            $quote,
            'quotes.view.all',
            'quotes.view.own'
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->has($user, 'quotes.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @param Quote $quote
     *
     * @return bool
     */
    public function update(User $user, Quote $quote): bool
    {
        return $this->anyOrOwn(
            $user,
            $quote,
            'quotes.update.any',
            'quotes.update.own'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     *
     * @param Quote $quote
     *
     * @return bool
     */
    public function delete(User $user, Quote $quote): bool
    {
        return $this->anyOrOwn(
            $user,
            $quote,
            'quotes.delete.any',
            'quotes.delete.own'
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     *
     * @param Quote $quote
     *
     * @return bool
     */
    public function restore(User $user, Quote $quote): bool
    {
        return $this->anyOrOwn(
            $user,
            $quote,
            'quotes.restore.any',
            'quotes.restore.own'
        );
    }
}
