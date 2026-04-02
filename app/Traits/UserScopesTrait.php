<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Provides query scope methods for the User model, allowing for easy filtering of users based on their roles and test status. This trait is intended to be used within the User model to encapsulate common query scopes related to user roles and attributes.
 * Query scopes defined in this trait include:
 * - scopeAdmins(): Scope a query to only include users with the administrator role.
 * - scopeSuperAdmins(): Scope a query to only include users with the super administrator role.
 * - scopeStandardUsers(): Scope a query to only include users with the standard user role.
 * - scopeReal(): Scope a query to exclude users that are marked as test records, allowing for filtering of only real user records in the system.
 * Note: The role IDs used in the scope methods are based on constants defined in the Role model, ensuring that the scopes remain consistent with the defined roles in the system.
 */
trait UserScopesTrait
{
    /**
     * Scope a query to only include admin users.
     *
     * This scope filters the query to include only users whose role_id matches the administrator role defined in the Role model. It allows for easy retrieval of all administrator users in the system.
     *
     * @param  Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role_id', \App\Models\Role::ROLE_ADMIN);
    }
 
    /**
     * Scope a query to only include super admin users.
     *
     * This scope filters the query to include only users whose role_id matches the super administrator role defined in the Role model. It allows for easy retrieval of all super administrator users in the system.
     *
     * @param  Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeSuperAdmins(Builder $query): Builder
    {
        return $query->where('role_id', \App\Models\Role::ROLE_SUPER_ADMIN);
    }
 
    /**
     * Scope a query to only include standard users.
     *
     * This scope filters the query to include only users whose role_id matches the standard user role defined in the Role model. It allows for easy retrieval of all standard users in the system.
     *
     * @param  Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeStandardUsers(Builder $query): Builder
    {
        return $query->where('role_id', \App\Models\Role::ROLE_USER);
    }
 
    /**
     * Scope a query to exclude test records.
     *
     * This scope filters the query to include only users where the 'is_test' attribute is false, effectively excluding any users that are marked as test records. This is useful for ensuring that queries return only real user records in the system.
     *
     * @param  Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
