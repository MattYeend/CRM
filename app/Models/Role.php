<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Represents a user role within the system.
 *
 * Roles define access levels and group permissions that can be assigned
 * to users. Each role may be linked to multiple permissions and users,
 * enabling flexible role-based access control (RBAC).
 *
 * Roles also support related activities such as attachments, tasks,
 * notes, and audit tracking via associated models.
 *
 * Relationships defined in this model include:
 * - users(): One-to-many relationship to User records assigned to this
 *      role.
 * - permissions(): Many-to-many relationship to Permission records
 *      associated with this role.
 * - attachments(): Polymorphic one-to-many relationship to Attachment
 *      records associated with the role.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the role.
 * - tasks(): Polymorphic one-to-many relationship to Task records
 *      associated with the role.
 * - notes(): Polymorphic one-to-many relationship to Note records
 *      associated with the role.
 * Example usage of relationships:
 * ```php
 * $role = Role::find(1);
 * $users = $role->users; // Get all users assigned to this role
 * $permissions = $role->permissions; // Get all permissions for this role
 * $tasks = $role->tasks; // Get all tasks associated with this role
 * $notes = $role->notes; // Get all notes associated with this role
 * ```
 *
 * Accessor methods include:
 * - getIsAdminAttribute(): Returns a boolean indicating whether this role
 *      is the administrator role.
 * - getIsSuperAdminAttribute(): Returns a boolean indicating whether this
 *      role is the super administrator role.
 * - getUserCountAttribute(): Returns the total number of users assigned
 *      to this role.
 * Example usage of accessors:
 * ```php
 * $role = Role::find(1);
 * $isAdmin = $role->is_admin; // Check if this is the admin role
 * $isSuperAdmin = $role->is_super_admin; // Check if this is the super admin
 * role
 * $userCount = $role->user_count; // Get the number of users in this role
 * ```
 *
 * Query scopes include:
 * - scopeAdmins($query): Filter the query to only include admin and super
 *      admin roles.
 * - scopeForUser($query, $userId): Filter the query to only include roles
 *      assigned to a given user.
 * - scopeWithPermission($query, $permission): Filter the query to only
 *      include roles that have a specific permission.
 * Example usage of query scopes:
 * ```php
 * $adminRoles = Role::admins()->get(); // Get admin-level roles
 * $userRoles = Role::forUser($userId)->get(); // Get roles for a user
 * $canEditRoles = Role::withPermission('edit')->get(); // Roles with edit
 * permission
 * ```
 */
class Role extends Model
{
    /**
     * @use HasFactory<\Database\Factories\RoleFactory>
     */
    use HasFactory;

    /**
     * Standard user role.
     */
    public const ROLE_USER = 1;

    /**
     * Administrator role.
     */
    public const ROLE_ADMIN = 2;

    /**
     * Super administrator role.
     */
    public const ROLE_SUPER_ADMIN = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'label',
    ];

    /**
     * Get the users assigned to this role.
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the permissions associated with this role.
     *
     * @return BelongsToMany<Permission>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Get all attachments associated with the role.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the role.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the role.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the role.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Determine whether this role is the administrator role.
     *
     * Returns true when the role's primary key matches the ROLE_ADMIN
     * constant. Useful for quick permission checks without loading the
     * full permissions relationship.
     *
     * @return bool
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->id === self::ROLE_ADMIN;
    }

    /**
     * Determine whether this role is the super administrator role.
     *
     * Returns true when the role's primary key matches the ROLE_SUPER_ADMIN
     * constant. Super admins are typically granted unrestricted access and
     * may bypass standard permission checks.
     *
     * @return bool
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->id === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Get the total number of users assigned to this role.
     *
     * Queries the users relationship to count matching records. Note that
     * this fires a query each time the accessor is called, so avoid using
     * it in loops without eager loading the count via withCount().
     *
     * @return int
     */
    public function getUserCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Scope a query to only include admin-level roles.
     *
     * Filters the query to include only roles whose ID matches either
     * ROLE_ADMIN or ROLE_SUPER_ADMIN. Useful for restricting queries
     * to elevated access roles without hardcoding IDs at the call site.
     *
     * @param  Builder<Role> $query The query builder instance.
     *
     * @return Builder<Role> The modified query builder instance.
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereIn('id', [
            self::ROLE_ADMIN,
            self::ROLE_SUPER_ADMIN,
        ]);
    }

    /**
     * Scope a query to only include roles assigned to a given user.
     *
     * Filters the query using a whereHas on the users relationship to
     * match the provided user ID. Useful for checking which roles are
     * active for a specific user without loading the user model first.
     *
     * @param  Builder<Role> $query The query builder instance.
     * @param  int $userId The ID of the user to filter by.
     *
     * @return Builder<Role> The modified query builder instance.
     */
    public function scopeForUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->whereHas(
            'users',
            fn (Builder $q) => $q->where('id', $userId)
        );
    }

    /**
     * Scope a query to only include roles that have a specific permission.
     *
     * Filters the query using a whereHas on the permissions relationship to
     * match the provided permission name. Useful for resolving which roles
     * grant a particular capability without loading the full permission set.
     *
     * @param  Builder<Role> $query The query builder instance.
     * @param  string $permission The permission name to filter by.
     *
     * @return Builder<Role> The modified query builder instance.
     */
    public function scopeWithPermission(
        Builder $query,
        string $permission
    ): Builder {
        return $query->whereHas(
            'permissions',
            fn (Builder $q) => $q->where('name', $permission)
        );
    }
}
