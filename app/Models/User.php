<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasTestPrefix;
use App\Traits\User\UserHelpers;
use App\Traits\User\UserRelationships;
use App\Traits\User\UserScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        HasTestPrefix,
        UserRelationships,
        UserHelpers,
        UserScopes;

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
