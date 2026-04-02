<?php

namespace App\Models;

use App\Traits\Activity\ActivityHelpers;
use App\Traits\Activity\ActivityRelationships;
use App\Traits\Activity\ActivityScopes;
use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents an activity record within the CRM.
 *
 * Activities are polymorphically associated with a subject model such as a
 * Company, Deal, Task, or User, and optionally attributed to a user. Each
 * activity carries a type string that is prefixed during test runs via the
 * HasTestPrefix trait.
 */
class Activity extends Model
{
    /**
     * @use HasFactory<\Database\Factories\ActivityFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix,
        ActivityHelpers,
        ActivityRelationships,
        ActivityScopes;

    /**
     * The fully-qualified class name used as the subject type for Company
     * activities.
     */
    public const ACTIVITY_COMPANY = Company::class;

    /**
     * The fully-qualified class name used as the subject type for Deal
     * activities.
     */
    public const ACTIVITY_DEAL = Deal::class;

    /**
     * The fully-qualified class name used as the subject type for Task
     * activities.
     */
    public const ACTIVITY_TASK = Task::class;

    /**
     * The fully-qualified class name used as the subject type for User
     * activities.
     */
    public const ACTIVITY_USER = User::class;

    /**
     * All valid subject types that an activity can be associated with.
     */
    public const ACTIVITY_TYPES = [
        self::ACTIVITY_COMPANY,
        self::ACTIVITY_DEAL,
        self::ACTIVITY_TASK,
        self::ACTIVITY_USER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'assigned_to',
        'type',
        'subject_type',
        'subject_id',
        'description',
        'is_test',
        'meta',
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
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the activity type, applying the test prefix when marked as a test.
     *
     * @param  string|null  $value  The raw type value from the database.
     *
     * @return string
     */
    public function getTypeAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Get the name of the user that owns the activity.
     *
     * Returns null if no user is associated.
     *
     * @return string|null
     */
    public function getUserNameAttribute(): ?string
    {
        return $this->user?->name;
    }
}
