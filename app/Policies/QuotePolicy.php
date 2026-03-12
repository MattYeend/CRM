<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\Role;
use App\Models\User;
use App\Traits\HandlesPolicyPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotePolicy
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
     * Determine whether the user can view any quotes.
     */
    public function viewAny(User $user): bool
    {
        return $this->has($user, 'quotes.view.all');
    }

    /**
     * Determine whether the user can view the quote.
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
     * Determine whether the user can create quotes.
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
     * Determine whether the user can update the quote.
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
     * Determine whether the user can delete the quote.
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
     * Determine whether the user can restore the quote.
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
