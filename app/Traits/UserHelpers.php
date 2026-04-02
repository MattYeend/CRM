<?php

namespace App\Traits;

use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Provides helper methods for the User model, such as permission checks and role management.
 * This trait is intended to be used within the User model to encapsulate common user-related logic.
 * Methods in this trait include:
 * - isSuperAdmin(): Check if the user is a super administrator.
 * - isAdmin(): Check if the user is an administrator.
 * - isUser(): Check if the user is a standard user.
 * - hasRole($role): Check if the user has a specific role, accepting either a role ID or name.
 * - permissions(): Retrieve the permissions assigned to the user via their role.
 * - getAllPermissions(): Get all permissions for the user, with caching for performance.
 * - hasPermission($permission): Check if the user has a specific permission.
 * - clearPermissionCache(): Clear the cached permissions for the user.
 * Note: The actual implementation of these methods is not provided here, as this trait serves as a placeholder for user helper methods that can be defined as needed.
 */
trait UserHelpers
{
    /**
     * Determine whether the user is a super administrator.
     *
     * A super administrator has all permissions and is typically used for the highest level of access control.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role_id === Role::ROLE_SUPER_ADMIN;
    }
 
    /**
     * Determine whether the user is an administrator.
     *
     * An administrator has elevated permissions compared to a standard user but may not have all permissions like a super administrator.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role_id === Role::ROLE_ADMIN;
    }
 
    /**
     * Determine whether the user is a standard user.
     *
     * A standard user typically has limited permissions and is the default role for most users in the system.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role_id === Role::ROLE_USER;
    }
 
    /**
     * Determine whether the user has a given role.
     *
     * Accepts either a role ID or role name.
     *
     * @param  int|string $role The role ID or name.
     *
     * @return bool
     */
    public function hasRole(int|string $role): bool
    {
        if (! $this->role) {
            return false;
        }
 
        if (is_int($role)) {
            return $this->role->id === $role;
        }
 
        return $this->role->name === $role;
    }
 
    /**
     * Get the permissions assigned to the user via their role.
     *
     * If the user has a role, it retrieves the permissions associated with that role and returns their names as a unique collection. If the user does not have a role, it returns an empty collection.
     * This method assumes that the Role model has a relationship defined to retrieve its permissions, and that each permission has a 'name' attribute.
     *
     * @return Collection<int,string>
     */
    public function permissions(): Collection
    {
        return $this->role
            ? $this->role->permissions->pluck('name')->unique()
            : collect();
    }
 
    /**
     * Get all permissions for the user.
     *
     * Results are cached for 60 minutes to improve performance.
     * This method retrieves the permissions for the user and caches the result using Laravel's caching mechanism. The cache key is based on the user's ID to ensure that permissions are stored separately for each user. If the permissions are not already cached, it calls the permissions() method to retrieve them and stores the result in the cache for future use.
     * @return array<int,string>
     */
    public function getAllPermissions(): array
    {
        return Cache::remember(
            "user_permissions_{$this->id}",
            60,
            fn () => $this->permissions()->toArray()
        );
    }
 
    /**
     * Determine whether the user has a given permission.
     *
     * This method checks if the specified permission is present in the user's permissions. It retrieves all permissions for the user (potentially from cache) and checks if the given permission name exists in that list. This allows for efficient permission checks without needing to query the database every time, as permissions are cached for 60 minutes.
     *
     * @param  string $permission The permission name.
     *
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getAllPermissions());
    }
 
    /**
     * Clear the cached permissions for the user.
     *
     * This method is useful when the user's permissions have changed (e.g., due to a role change or permission update) and you want to ensure that the cache is refreshed with the latest permissions. It uses Laravel's Cache facade to remove the cached permissions for the user based on their ID.
     *
     * @return void
     */
    public function clearPermissionCache(): void
    {
        Cache::forget("user_permissions_{$this->id}");
    }
}
