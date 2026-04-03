<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Billable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Represents an authenticated user within the system.
 *
 * Users support authentication, billing, notifications, and API access.
 * They are associated with roles and permissions for access control,
 * and may own or be assigned various entities such as deals, tasks,
 * notes, and learnings.
 *
 * Permissions are derived from the user's role and cached for performance.
 * Users may also be marked as test records, in which case certain
 * attributes (e.g. name) are automatically prefixed.
 *
 * This model includes traits for factory support, notifications, two-factor
 * authentication, soft deletes, API tokens, billing.
 *
 * Relationships defined in this model include:
 * - role(): Defines a belongs-to relationship with the Role model,
 *      allowing access to the user's role information.
 * - jobTitle(): Defines a belongs-to relationship with the JobTitle model,
 *      allowing access to the user's job title information.
 * - deals(): Defines a has-many relationship with the Deal model, allowing
 *      access to all deals where the user is the owner.
 * - tasks(): Defines a has-many relationship with the Task model, allowing
 *      access to all tasks where the user is the assignee.
 * - notes(): Defines a has-many relationship with the Note model, allowing
 *      access to all notes created by the user.
 * - learnings(): Defines a many-to-many relationship with the Learning model
 *      through the LearningUser pivot model, allowing access to all learnings
 *      associated with the user along with additional pivot data.
 * - attachment(): Defines a polymorphic one-to-many relationship with the
 *      Attachment model, allowing access to all attachments where the user
 *      is the attachable entity.
 * - activity(): Defines a polymorphic one-to-many relationship with the
 *      Activity model, allowing access to all activities where the user is
 *      the subject entity.
 * - tasking(): Defines a polymorphic one-to-many relationship with the
 *      Task model, allowing access to all tasks where the user is the
 *      taskable entity.
 * - note(): Defines a polymorphic one-to-many relationship with the Note
 *      model, allowing access to all notes where the user is the notable
 *      entity.
 * - createdTasks(): Defines a has-many relationship with the Task model,
 *     allowing access to all tasks where the user is the creator.
 * - assignedTasks(): Defines a has-many relationship with the Task model,
 *      allowing access to all tasks where the user is the assignee.
 * - createdActivities(): Defines a has-many relationship with the Activity
 *      model, allowing access to all activities where the user is the
 *      creator.
 * - assignedActivities(): Defines a has-many relationship with the Activity
 *      model, allowing access to all activities where the user is the
 *      assignee.
 * - createdOrders(): Defines a has-many relationship with the Order model,
 *      allowing access to all orders where the user is the creator.
 * - ownedDeals(): Defines a has-many relationship with the Deal model, allowing
 *      access to all deals where the user is the owner.
 * - leads(): Defines a has-many relationship with the Lead model, allowing
 *      access to all leads where the user is the assignee.
 * - createdLearning(): Defines a has-many relationship with the Learning model,
 *      allowing access to all learning records where the user is the creator.
 * - assignedLearning(): Defines a many-to-many relationship with the Learning
 *      model through the LearningUser pivot model, allowing access to all
 *      learnings associated with the user along with additional pivot data,
 *      specifically focusing on the learnings assigned to the user as opposed
 *      to those created by the user.
 *
 * Example usage of these relationships in code might look like:
 * ```php
 * $user = User::find(1);
 * $role = $user->role; // Get the user's role
 * $jobTitle = $user->jobTitle; // Get the user's job title
 * $deals = $user->deals; // Get all deals owned by the user
 * $tasks = $user->tasks; // Get all tasks assigned to the user
 * $notes = $user->notes; // Get all notes created by the user
 * $learnings = $user->learnings; // Get all learnings associated
 * with the user
 * $attachments = $user->attachment; // Get all attachments for the user
 * $activities = $user->activity; // Get all activities for the user
 * $tasking = $user->tasking; // Get all tasks where the user is the
 * taskable entity
 * $note = $user->note; // Get all notes where the user is the notable
 * entity
 * $createdTasks = $user->createdTasks; // Get all tasks created by
 * the user
 * $assignedTasks = $user->assignedTasks; // Get all tasks assigned to
 * the user
 * $createdActivities = $user->createdActivities; // Get all activities
 * created by the user
 * $assignedActivities = $user->assignedActivities; // Get all activities
 * assigned to the user
 * $createdOrders = $user->createdOrders; // Get all orders created by
 * the user
 * $ownedDeals = $user->ownedDeals; // Get all deals owned by the user
 * $leads = $user->leads; // Get all leads assigned to the user
 * $createdLearning = $user->createdLearning; // Get all learning records
 * created by the user
 * $assignedLearning = $user->assignedLearning; // Get all learning records
 * assigned to the user
 * ```
 *
 * Methods in this model include:
 * - isSuperAdmin(): Check if the user is a super administrator.
 * - isAdmin(): Check if the user is an administrator.
 * - isUser(): Check if the user is a standard user.
 * - hasRole($role): Check if the user has a specific role, accepting
 *      either a role ID or name.
 * - permissions(): Retrieve the permissions assigned to the user via
 *      their role.
 * - getAllPermissions(): Get all permissions for the user, with caching
 *      for performance.
 * - hasPermission($permission): Check if the user has a specific permission.
 * - clearPermissionCache(): Clear the cached permissions for the user.
 * Example usage of these methods in code might look like:
 * ```php
 * $user = User::find(1);
 * if ($user->isSuperAdmin()) {
 *   // User has super administrator privileges
 * } elseif ($user->isAdmin()) {
 *  // User has administrator privileges
 * } elseif ($user->isUser()) {
 * // User has standard user privileges
 * }
 * if ($user->hasPermission('edit_posts')) {
 * // User has permission to edit posts
 * }
 * $user->clearPermissionCache(); // Clear cached permissions if needed
 * ```
 *
 * Query scopes defined in this model include:
 * - scopeAdmins(): Scope a query to only include users with the administrator
 *      role.
 * - scopeSuperAdmins(): Scope a query to only include users with the super
 *      administrator role.
 * - scopeStandardUsers(): Scope a query to only include users with the
 *      standard user role.
 * - scopeReal(): Scope a query to exclude users that are marked as test
 *      records, allowing for filtering of only real user records in the
 *      system.
 *
 * Example usage of these scopes in a query might look like:
 * ```php
 * $admins = User::admins()->get(); // Get all administrator users
 * $superAdmins = User::superAdmins()->get(); // Get all super administrator
 * users
 * $standardUsers = User::standardUsers()->get(); // Get all standard users
 * $realUsers = User::real()->get(); // Get all users that are not marked
 * as test records
 * ```
 */
class User extends Authenticatable
{
    /**
     * @use HasFactory<\Database\Factories\UserFactory>
     * @use Notifiable<\Illuminate\Notifications\Notifiable>
     * @use TwoFactorAuthenticatable<\Laravel\Fortify\TwoFactorAuthenticatable>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasApiTokens<\Laravel\Sanctum\HasApiTokens>
     * @use Billable<\Laravel\Cashier\Billable>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     * @use UserRelationships<\App\Traits\User\UserRelationships>
     * @use UserHelpers<\App\Traits\User\UserHelpers>
     * @use UserScopes<\App\Traits\User\UserScopes>
     */
    use HasFactory,
        Notifiable,
        TwoFactorAuthenticatable,
        SoftDeletes,
        HasApiTokens,
        Billable,
        HasTestPrefix;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'job_title_id',
        'role_id',
        'is_test',
        'meta',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'restored_at',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'permissions' => 'array',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the role assigned to the user.
     *
     * Defines a belongs-to relationship between the User and Role models,
     * allowing access to the user's role information.
     *
     * @return BelongsTo<Role,self>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the job title associated with the user.
     *
     * Defines a belongs-to relationship between the User and JobTitle models,
     * allowing access to the user's job title information.
     *
     * @return BelongsTo<JobTitle,self>
     */
    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    /**
     * Get the deals owned by the user.
     *
     * Defines a has-many relationship between the User and Deal models,
     * allowing access to all deals where the user is the owner.
     *
     * @return HasMany<Deal>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    /**
     * Get the tasks assigned to the user.
     *
     * Defines a has-many relationship between the User and Task models,
     * allowing access to all tasks where the user is the assignee.
     *
     * @return HasMany<Task>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get the notes created by the user.
     *
     * Defines a has-many relationship between the User and Note models,
     * allowing access to all notes created by the user.
     *
     * @return HasMany<Note>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'user_id');
    }

    /**
     * Get the learnings associated with the user.
     *
     * Defines a many-to-many relationship between the User and Learning models
     * hrough the LearningUser pivot model. This allows access to all learnings
     * associated with the user, along with additional pivot data such as
     * completion status, timestamps, and metadata.
     * The pivot data includes:
     * - `is_complete`: Indicates whether the learning has been
     *      completed by the user.
     * - `user_id`: The ID of the user associated with the learning.
     * - `completed_at`: The timestamp when the learning was completed.
     * - `is_test`: Indicates whether the learning is a test.
     * - `meta`: Additional metadata related to the learning.
     * - `created_by`: The ID of the user who created the learning record.
     * - `updated_by`: The ID of the user who last updated the learning record.
     *
     * @return BelongsToMany<Learning>
     */
    public function learnings(): BelongsToMany
    {
        return $this->belongsToMany(Learning::class)
            ->using(LearningUser::class)
            ->withPivot([
                'is_complete',
                'user_id',
                'completed_at',
                'is_test',
                'meta',
                'created_by',
                'updated_by',
            ])
            ->withTimestamps();
    }

    /**
     * Get all attachments associated with the user.
     *
     * Defines a polymorphic one-to-many relationship between the User
     * and Attachment models, allowing access to all attachments where
     * the user is the attachable entity. This means that the user can
     * have multiple attachments, and each attachment can belong to
     * different types of entities in the system (not just users).
     *
     * @return MorphMany<Attachment>
     */
    public function attachment(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the user.
     *
     * Defines a polymorphic one-to-many relationship between the User
     * and Activity models, allowing access to all activities where the
     * user is the subject entity. This means that the user can have
     * multiple activities, and each activity can belong to different
     * types of entities in the system (not just users).
     *
     * @return MorphMany<Activity>
     */
    public function activity(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks where the user is the taskable entity.
     *
     * Defines a polymorphic one-to-many relationship between the User
     * and Task models, allowing access to all tasks where the user is
     * the taskable entity. This means that the user can have multiple
     * tasks assigned to them as the taskable entity, and each task can
     * belong to different types of entities in the system
     * (not just users).
     *
     * @return MorphMany<Task>
     */
    public function tasking(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the user as a notable entity.
     *
     * Defines a polymorphic one-to-many relationship between the User
     * and Note models, allowing access to all notes where the user is
     * the notable entity. This means that the user can have multiple
     * notes associated with them as the notable entity, and each note
     * can belong to different types of entities in the system
     * (not just users).
     *
     * @return MorphMany<Note>
     */
    public function note(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Tasks created by this user.
     *
     * Defines a has-many relationship between the User and Task models,
     * allowing access to all tasks where the user is the creator. This
     * relationship specifically focuses on the tasks that were created
     * by the user, as opposed to those assigned to the user or where
     * the user is the taskable entity.
     *
     * @return HasMany<Task>
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Tasks assigned to this user.
     *
     * Defines a has-many relationship between the User and Task models,
     * allowing access to all tasks where the user is the assignee. This
     * relationship specifically focuses on the tasks that are assigned
     * to the user, as opposed to those created by the user or where the
     * user is the taskable entity.
     *
     * @return HasMany<Task>
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Activities created by this user.
     *
     * Defines a has-many relationship between the User and Activity models,
     * allowing access to all activities where the user is the creator.
     * This relationship specifically focuses on the activities that were
     * created by the user, as opposed to those assigned to the user or
     * where the user is the subject of the activity.
     *
     * @return HasMany<Activity>
     */
    public function createdActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'created_by');
    }

    /**
     * Activities assigned to this user.
     *
     * Defines a has-many relationship between the User and Activity models,
     * allowing access to all activities where the user is the assignee.
     * This relationship specifically focuses on the activities that are
     * assigned to the user, as opposed to those created by the user or
     * where the user is the subject of the activity.
     *
     * @return HasMany<Activity>
     */
    public function assignedActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'assigned_to');
    }

    /**
     * Orders created by this user.
     *
     * Defines a has-many relationship between the User and Order models,
     * allowing access to all orders where the user is the creator. This
     * relationship specifically focuses on the orders that were created
     * by the user, as opposed to those assigned to the user or owned
     * by the user.
     *
     * @return HasMany<Order>
     */
    public function createdOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    /**
     * Deals owned by this user.
     *
     * Defines a has-many relationship between the User and Deal models,
     * allowing access to all deals where the user is the owner. This
     * relationship specifically focuses on the deals that are owned by
     * the user, as opposed to those created by the user or assigned to
     * the user.
     *
     * @return HasMany<Deal>
     */
    public function ownedDeals(): HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    /**
     * Leads assigned to this user.
     *
     * Defines a has-many relationship between the User and Lead models,
     * allowing access to all leads where the user is the assignee.
     * This relationship specifically focuses on the leads that are
     * assigned to the user, as opposed to those created by the user
     * or owned by the user.
     *
     * @return HasMany<Lead>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    /**
     * Learning records created by this user.
     *
     * Defines a has-many relationship between the User and Learning
     * models, allowing access to all learning records where the user
     * is the creator. This relationship specifically focuses on the
     * learnings that were created by the user, as opposed to those
     * assigned to the user.
     *
     * @return HasMany<Learning>
     */
    public function createdLearning(): HasMany
    {
        return $this->hasMany(Learning::class, 'created_by');
    }

    /**
     * Learning records assigned to this user.
     *
     * Defines a many-to-many relationship between the User and
     * Learning models through the LearningUser pivot model,
     * allowing access to all learnings associated with the user
     * along with additional pivot data such as completion status,
     * timestamps, and metadata. This relationship specifically
     * focuses on the learnings that are assigned to the user,
     * as opposed to those created by the user.
     *
     * @return BelongsToMany<Learning>
     */
    public function assignedLearning(): BelongsToMany
    {
        return $this->belongsToMany(Learning::class)
            ->using(LearningUser::class)
            ->withPivot([
                'is_complete',
            ])
            ->withTimestamps();
    }

    /**
     * Determine whether the user is a super administrator.
     *
     * A super administrator has all permissions and is typically
     * used for the highest level of access control.
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
     * An administrator has elevated permissions compared to a
     * standard user but may not have all permissions like a
     * super administrator.
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
     * A standard user typically has limited permissions and is
     * the default role for most users in the system.
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
     * If the user has a role, it retrieves the permissions associated
     * with that role and returns their names as a unique collection.
     * If the user does not have a role, it returns an empty collection.
     * This method assumes that the Role model has a relationship defined
     * to retrieve its permissions, and that each permission has a 'name'
     * attribute.
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
     * This method retrieves the permissions for the user and caches
     * the result using Laravel's caching mechanism. The cache key
     * is based on the user's ID to ensure that permissions are stored
     * separately for each user. If the permissions are not already
     * cached, it calls the permissions() method to retrieve them and
     * stores the result in the cache for future use.
     *
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
     * This method checks if the specified permission is present in
     * the user's permissions. It retrieves all permissions for the
     * user (potentially from cache) and checks if the given permission
     * name exists in that list. This allows for efficient permission
     * checks without needing to query the database every time, as
     * permissions are cached for 60 minutes.
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
     * This method is useful when the user's permissions have changed
     * (e.g., due to a role change or permission update) and you want
     * to ensure that the cache is refreshed with the latest permissions.
     * It uses Laravel's Cache facade to remove the cached permissions
     * for the user based on their ID.
     *
     * @return void
     */
    public function clearPermissionCache(): void
    {
        Cache::forget("user_permissions_{$this->id}");
    }

    /**
     * Get the formatted user name.
     *
     * Applies a test prefix when the user is marked as a test record.
     *
     * This accessor method formats the user's name by applying a test prefix
     * if the user is marked as a test record. The prefixTest() method is
     * assumed to be defined elsewhere in the User model or a related trait,
     * and it adds a specific prefix to the name when the user is a test
     * record. This allows for easy identification of test users in the
     * system while maintaining the original name format for real users.
     *
     * @param  string|null $value The raw user name from the database.
     *
     * @return string The formatted user name.
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Scope a query to only include admin users.
     *
     * This scope filters the query to include only users whose role_id
     * matches the administrator role defined in the Role model. It allows
     * for easy retrieval of all administrator users in the system.
     *
     * @param  Builder<User> $query The query builder instance.
     *
     * @return Builder<User> The modified query builder instance with
     * the administrator filter applied.
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role_id', Role::ROLE_ADMIN);
    }

    /**
     * Scope a query to only include super admin users.
     *
     * This scope filters the query to include only users whose role_id
     * matches the super administrator role defined in the Role model.
     * It allows for easy retrieval of all super administrator users in
     * the system.
     *
     * @param  Builder<User> $query The query builder instance.
     *
     * @return Builder<User> The modified query builder instance with
     * the super administrator filter applied.
     */
    public function scopeSuperAdmins(Builder $query): Builder
    {
        return $query->where('role_id', Role::ROLE_SUPER_ADMIN);
    }

    /**
     * Scope a query to only include standard users.
     *
     * This scope filters the query to include only users whose role_id
     * matches the standard user role defined in the Role model. It
     * allows for easy retrieval of all standard users in the system.
     *
     * @param  Builder<User> $query The query builder instance.
     *
     * @return Builder<User> The modified query builder instance with the
     * standard user filter applied.
     */
    public function scopeStandardUsers(Builder $query): Builder
    {
        return $query->where('role_id', Role::ROLE_USER);
    }

    /**
     * Scope a query to exclude test records.
     *
     * This scope filters the query to include only users where the
     * 'is_test' attribute is false, effectively excluding any users
     * that are marked as test records. This is useful for ensuring
     * that queries return only real user records in the system.
     *
     * @param  Builder<User> $query The query builder instance.
     *
     * @return Builder<User> The modified query builder instance
     * with the test records excluded.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * The "booted" method of the model.
     *
     * Clears the user's cached permissions whenever the model is
     * created or updated to ensure consistency.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->clearPermissionCache();
        });

        static::updated(function (User $user) {
            $user->clearPermissionCache();
        });
    }
}
