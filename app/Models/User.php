<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Console\View\Components\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,
        Notifiable,
        TwoFactorAuthenticatable,
        SoftDeletes,
        HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
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
     * The roles that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Attach roles to the user for testing purposes.
     *
     * @param int $count
     *
     * @return static
     */
    public function withRoles(int $count = 1): static
    {
        return $this->hasAttached(
            Role::factory()->count($count)
        );
    }

    /**
     * The permissions that belong to the user through roles.
     *
     * @return \Illuminate\Support\Collection
     */
    public function permissions(): Collection
    {
        return $this->roles
            ->load('permissions')
            ->flatMap(fn ($role) => $role->permissions->pluck('name'))
            ->unique();
    }

    /**
     * Get all permissions for the user, caching the result for 60 minutes.
     *
     * @return array<int, string>
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
        return $this->roles()
            ->when(is_int($role), fn ($q) => $q->where('roles.id', $role))
            ->when(is_string($role), fn ($q) => $q->where('roles.name', $role))
            ->exists();
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    /**
     * The tasks assigned to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * The notes created by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * The learnings belonging to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function learnings(): BelongsToMany
    {
        return $this->belongsToMany(Learning::class)
            ->withPivot(['is_completed', 'completed_by', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
