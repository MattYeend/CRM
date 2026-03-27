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
        'trail_ends_at',
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
     * Get the attributes that should be cast.
     *
     * @return array<string,string>
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
     * The roles that belong to the user.
     *
     * @return BelongsTo<Role,User>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Super admin user role
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role_id === Role::ROLE_SUPER_ADMIN;
    }

    /**
     * Admin user role
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role_id === Role::ROLE_ADMIN;
    }

    /**
     * User role
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role_id === Role::ROLE_USER;
    }

    /**
     * The permissions that belong to the user through roles.
     *
     * @return Collection
     */
    public function permissions(): Collection
    {
        return $this->role
            ? $this->role->permissions->pluck('name')->unique()
            : collect();
    }

    /**
     * Get all permissions for the user, caching the result for 60 minutes.
     *
     * @return array<int,string>
     */
    public function getAllPermissions(): array
    {
        if (app()->environment('testing')) {
            return $this->permissions()->toArray();
        }

        return Cache::remember(
            "user_permissions_{$this->id}",
            60,
            fn () => $this->permissions()->toArray()
        );
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getAllPermissions());
    }

    /**
     * Check if the user has a specific role.
     *
     * @param int $roleId
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
     * The deals owned by the user.
     *
     * @return HasMany<Deal>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    /**
     * The tasks assigned to the user.
     *
     * @return HasMany<Task>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get notes with the user id.
     *
     * @return HasMany<Note>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'user_id');
    }

    /**
     * The learnings belonging to the user.
     *
     * @return BelongsToMany<Learning>
     */
    public function learnings(): BelongsToMany
    {
        return $this->belongsToMany(Learning::class)
            ->using(LearningUser::class)
            ->withPivot([
                'is_complete', 'user_id', 'completed_at', 'is_test', 'meta',
                'created_by', 'updated_by',
            ])
            ->withTimestamps();
    }

    /**
     * Get all of the user attachments.
     *
     * @return MorphMany<Attachment>
     */
    public function attachment(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all of the user activities.
     *
     * @return MorphMany<Activity>
     */
    public function activity(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all of the user tasks.
     *
     * @return MorphMany<Task>
     */
    public function tasking(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all of the user notes.
     *
     * @return MorphMany<Note>
     */
    public function note(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the job title of the user
     *
     * @return BelongsTo<JobTitle,User>
     */
    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    /**
     * Get the user name, applies the test prefix when the user is marked
     * as a test.
     *
     * @param  string|null  $value  The raw user title from the database.
     *
     * @return string
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->clearPermissionCache();
        });

        static::updated(function (User $user) {
            if ($user->wasChanged('role_id')) {
                $user->clearPermissionCache();
            }
        });
    }
}
