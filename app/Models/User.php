<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasTestPrefix;
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
     * @return BelongsTo<Role,User>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Determine whether the user is a super administrator.
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
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role_id === Role::ROLE_ADMIN;
    }

    /**
     * Determine whether the user is a standard user.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role_id === Role::ROLE_USER;
    }

    /**
     * Get the permissions assigned to the user via their role.
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
     * @param  string $permission The permission name.
     *
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getAllPermissions());
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
     * Clear the cached permissions for the user.
     *
     * @return void
     */
    public function clearPermissionCache(): void
    {
        Cache::forget("user_permissions_{$this->id}");
    }

    /**
     * Get the deals owned by the user.
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
     * @return HasMany<Task>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get the notes created by the user.
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
     * Includes pivot data such as completion status and timestamps.
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
     * @return MorphMany<Attachment>
     */
    public function attachment(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the user.
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
     * @return MorphMany<Task>
     */
    public function tasking(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the user as a notable entity.
     *
     * @return MorphMany<Note>
     */
    public function note(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the job title associated with the user.
     *
     * @return BelongsTo<JobTitle,User>
     */
    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    /**
     * Get the formatted user name.
     *
     * Applies a test prefix when the user is marked as a test record.
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
