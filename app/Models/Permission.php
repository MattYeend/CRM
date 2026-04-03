<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a permission that can be assigned to roles.
 *
 * Permissions are identified by a unique name and an optional human-readable
 * label. Helper methods are provided to check whether a permission is
 * associated with one or more roles by name.
 *
 * Relationships defined in this model include:
 * - roles(): Many-to-many relationship to Role records that have been
 *      granted this permission.
 * - attachments(): Polymorphic one-to-many relationship to Attachment
 *      records associated with the permission.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the permission.
 * - tasks(): Polymorphic one-to-many relationship to Task records
 *      associated with the permission.
 * - notes(): Polymorphic one-to-many relationship to Note records
 *      associated with the permission.
 * - creator(): Belongs-to relationship to the User who created the
 *      permission.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      permission.
 * - deleter(): Belongs-to relationship to the User who deleted the
 *      permission (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the
 *      permission (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $permission = Permission::find(1);
 * $roles = $permission->roles; // Get all roles that have this permission
 * $attachments = $permission->attachments; // Get all attachments associated
 * with this permission
 * $tasks = $permission->tasks; // Get all tasks associated with this permission
 * $notes = $permission->notes; // Get all notes associated with this permission
 * $creator = $permission->creator; // Get the user that created this permission
 * $updater = $permission->updater; // Get the user that last updated this
 * permission
 * $deleter = $permission->deleter; // Get the user that deleted this permission
 * (if applicable)
 * $restorer = $permission->restorer; // Get the user that restored this
 * permission (if applicable)
 * ```
 *
 * Helper methods include:
 * - hasRole($roleName): Returns true if this permission is assigned to the
 *      named role.
 * - hasAnyRole($roleNames): Returns true if this permission is assigned to
 *      at least one of the named roles.
 * - hasAllRoles($roleNames): Returns true if this permission is assigned to
 *      every one of the named roles.
 * Example usage of helper methods:
 * ```php
 * $permission = Permission::find(1);
 * $isAdmin = $permission->hasRole('admin'); // Check single role
 * $isElevated = $permission->hasAnyRole(['admin', 'super']); // Check any match
 * $isBoth = $permission->hasAllRoles(['admin', 'super']); // Check all match
 * ```
 *
 * Accessor methods include:
 * - getIsAssignedAttribute(): Returns a boolean indicating whether this
 *      permission has been assigned to at least one role.
 * - getRoleCountAttribute(): Returns the total number of roles that have
 *      been granted this permission.
 * Example usage of accessors:
 * ```php
 * $permission = Permission::find(1);
 * $isAssigned = $permission->is_assigned; // Check if any role has
 * this permission
 * $roleCount = $permission->role_count; // Get the number of roles
 * with this permission
 * ```
 *
 * Query scopes include:
 * - scopeAssigned($query): Filter the query to only include permissions
 *      that have been assigned to at least one role.
 * - scopeUnassigned($query): Filter the query to only include permissions
 *      that have not been assigned to any role.
 * - scopeForRole($query, $roleId): Filter the query to only include
 *      permissions assigned to a given role.
 * - scopeSearch($query, $term): Filter the query by name or label using a
 *      single search term.
 * Example usage of query scopes:
 * ```php
 * $assigned = Permission::assigned()->get(); // Permissions in use
 * $unassigned = Permission::unassigned()->get(); // Orphaned permissions
 * $rolePerms = Permission::forRole($roleId)->get(); // Permissions for a role
 * $results = Permission::search('edit')->get(); // Search by name
 * ```
 */
class Permission extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PermissionFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'label',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'restored_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the roles that have this permission.
     *
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Get all attachments associated with the permission.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the permission.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the permission.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the permission.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the user that created the permission.
     *
     * @return BelongsTo<User,Permission>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the permission.
     *
     * @return BelongsTo<User,Permission>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the permission.
     *
     * @return BelongsTo<User,Permission>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the permission.
     *
     * @return BelongsTo<User,Permission>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Determine whether the permission is assigned to a specific role.
     *
     * @param  string $roleName The name of the role to check.
     *
     * @return bool True if the role is associated with this permission.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Determine whether the permission is assigned to any of the given roles.
     *
     * Returns true as soon as at least one of the provided role names is found
     * in the permission's associated roles. Useful for broad access checks
     * where any elevated role should grant the permission.
     *
     * @param  array<int,string> $roleNames The role names to check against.
     *
     * @return bool True if at least one of the roles is associated.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Determine whether the permission is assigned to all of the given roles.
     *
     * Counts the matching role associations and compares against the number
     * of names provided. Returns true only when every named role is associated
     * with this permission.
     *
     * @param  array<int,string> $roleNames The role names to check against.
     *
     * @return bool True if every role in the array is associated.
     */
    public function hasAllRoles(array $roleNames): bool
    {
        $rolesCount = $this->roles()->whereIn('name', $roleNames)->count();

        return $rolesCount === count($roleNames);
    }

    /**
     * Determine whether this permission has been assigned to at least one role.
     *
     * Useful for identifying orphaned permissions that exist in the system
     * but have not yet been attached to any role.
     *
     * @return bool
     */
    public function getIsAssignedAttribute(): bool
    {
        return $this->roles()->exists();
    }

    /**
     * Get the total number of roles that have been granted this permission.
     *
     * Fires a query each time the accessor is called. Avoid using it in
     * loops without eager loading the count via withCount('roles').
     *
     * @return int
     */
    public function getRoleCountAttribute(): int
    {
        return $this->roles()->count();
    }

    /**
     * Scope a query to only include permissions that have been assigned
     * to at least one role.
     *
     * Uses a whereHas constraint on the roles relationship. Useful for
     * filtering out orphaned permissions in management interfaces.
     *
     * @param  Builder<Permission> $query The query builder instance.
     *
     * @return Builder<Permission> The modified query builder instance.
     */
    public function scopeAssigned(Builder $query): Builder
    {
        return $query->whereHas('roles');
    }

    /**
     * Scope a query to only include permissions that have not been assigned
     * to any role.
     *
     * Uses a whereDoesntHave constraint on the roles relationship. Useful
     * for identifying and cleaning up orphaned permission records.
     *
     * @param  Builder<Permission> $query    query builder instance.
     *
     * @return Builder<Permission> The modified query builder instance.
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereDoesntHave('roles');
    }

    /**
     * Scope a query to only include permissions assigned to a given role.
     *
     * Filters using a whereHas constraint on the roles relationship matched
     * by role ID. Useful for building role-specific permission management
     * screens without loading the Role model directly.
     *
     * @param  Builder<Permission> $query The query builder instance.
     * @param  int $roleId The ID of the role to filter by.
     *
     * @return Builder<Permission> The modified query builder instance.
     */
    public function scopeForRole(
        Builder $query,
        int $roleId
    ): Builder {
        return $query->whereHas(
            'roles',
            fn (Builder $q) => $q->where('id', $roleId)
        );
    }

    /**
     * Scope a query to search permissions by name or label using a single
     * search term.
     *
     * Wraps the conditions in a grouped where clause to ensure correct
     * boolean precedence when chained with other scopes.
     *
     * @param  Builder<Permission> $query The query builder instance.
     * @param  string $term The search term to match against.
     *
     * @return Builder<Permission> The modified query builder instance.
     */
    public function scopeSearch(
        Builder $query,
        string $term
    ): Builder {
        $like = "%{$term}%";

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('label', 'like', $like);
        });
    }
}
