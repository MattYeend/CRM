<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents an activity record within the CRM.
 *
 * Activities are polymorphically associated with a subject model such as a
 * Company, Deal, Task, or User, and optionally attributed to a user. Each
 * activity carries a type string that is prefixed during test runs via the
 * HasTestPrefix trait.
 *
 * Relationships defined in this model include:
 * - user(): The user assigned to the activity.
 * - subject(): The polymorphic subject model associated with
 *      the activity.
 * - creator(): The user who created the activity.
 * - updater(): The user who last updated the activity.
 * - deleter(): The user who deleted the activity.
 * - restorer(): The user who restored the activity.
 * - attachments(): All attachments associated with the activity.
 * - tasks(): All tasks associated with the activity.
 * - notes(): All notes associated with the activity.
 * Example usage of these relationships in code might look like:
 * ```php
 * $activity = Activity::find(1);
 * $user = $activity->user; // Get the assigned user
 * $subject = $activity->subject; // Get the associated subject model
 * $creator = $activity->creator; // Get the user who created
 * the activity
 * $attachments = $activity->attachments; // Get all attachments
 * for the activity
 * $tasks = $activity->tasks; // Get all tasks for the activity
 * $notes = $activity->notes; // Get all notes for the activity
 * ```
 *
 * Scopes defined in this model include:
 * - scopeAssignedTo($query, $userId): Scope a query to
 *      activities assigned to a specific user.
 * - scopeForSubjectType($query, $type): Scope a query
 *      to activities for a given subject type.
 * - scopeForSubject($query, $type, $id): Scope a query
 *      to activities for a specific subject instance.
 * - scopeReal($query): Scope a query to exclude test
 *      records.
 * Example usage of these scopes in a query might look like:
 * ```php
 * $activities = Activity::assignedTo($userId)
 *    ->forSubjectType(Company::class)
 *   ->real()
 *   ->get();
 * ```
 *
 * Methods defined in this model include:
 * - getTypeAttribute($value): Accessor for the activity
 *      type that applies test prefixing.
 * - getUserNameAttribute(): Accessor for the name of
 *      the assigned user, returning null if no user is associated.
 * - hasSubjectType($type): Utility method to check if the
 *      activity's subject type matches a given type.
 * Example usage of these methods in code might look like:
 * ```php
 * $activity = Activity::find(1);
 * $type = $activity->type; // Get the activity type with
 * test prefixing applied
 * $userName = $activity->user_name; // Get the name of the
 * assigned user, or null if no user is associated
 * $isCompanyActivity = $activity->hasSubjectType(Company::class);
 * // Check if the activity's subject type is Company
 * $isDealActivity = $activity->hasSubjectType(Deal::class); // Check if
 * the activity's subject type is Deal
 * ```
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
        HasTestPrefix;

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
     * Get the user that is assigned the activity.
     *
     * The assigned user is optional and may be null if the
     * activity is not currently assigned to anyone.
     * This relationship allows for easy retrieval of the
     * user responsible for the activity, if applicable.
     *
     * @return BelongsTo<User,Activity>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the polymorphic subject model this activity belongs to.
     *
     * The subject may be a Company, Deal, Task, or User as defined
     * in ACTIVITY_TYPES.
     *
     * This relationship allows the activity to be associated with any model
     * that implements the polymorphic interface, providing flexibility in
     * how activities are linked to various entities within the CRM. The
     * subject model can be accessed directly from the activity, enabling
     * developers to easily navigate from an activity to its associated
     * subject, regardless of the specific model type.
     *
     * @return MorphTo<Model,Activity>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that created the activity.
     *
     * This relationship allows for easy retrieval of the
     * user responsible for creating the activity record.
     *
     * @return BelongsTo<User,Activity>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the activity.
     *
     * This relationship allows for easy retrieval of the
     * user responsible for the most recent update to the
     * activity record.
     *
     * @return BelongsTo<User,Activity>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the activity.
     *
     * This relationship allows for easy retrieval of the
     * user responsible for deleting the activity record,
     * if it has been soft-deleted.
     *
     * @return BelongsTo<User,Activity>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the activity.
     *
     * This relationship allows for easy retrieval of the
     * user responsible for restoring the activity record,
     * if it has been soft-deleted and subsequently restored.
     *
     * @return BelongsTo<User,Activity>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the activity.
     *
     * This relationship allows for easy retrieval of all attachments
     * linked to the activity, enabling developers to access related
     * files and documents directly from the activity model. Attachments
     * can be added to activities to provide additional context or
     * information, and this relationship facilitates seamless
     * access to those attachments.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all tasks associated with the activity.
     *
     * This relationship allows for easy retrieval of all tasks
     * linked to the activity, enabling developers to access related
     * tasks directly from the activity model. Tasks can be created in
     * relation to activities to track specific actions or follow-ups,
     * and this relationship facilitates seamless access to those tasks
     * for better activity management and workflow integration.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the activity.
     *
     * This relationship allows for easy retrieval of all notes linked
     * to the activity, enabling developers to access related notes
     * directly from the activity model. Notes can be added to activities
     * to provide additional context, comments, or information, and
     * this relationship facilitates seamless access to those notes
     * for better activity documentation and communication.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the activity type, applying the test prefix when marked as a test.
     *
     * @param  string|null $value The raw type value from the database.
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

    /**
     * Determine whether the activity subject is of a given type.
     *
     * @param  string $type A fully-qualified class name (e.g. Company::class).
     *
     * @return bool
     */
    public function hasSubjectType(string $type): bool
    {
        return $this->subject_type === $type;
    }

    /**
     * Scope a query to activities assigned to a specific user.
     * This scope filters the query to include only activities
     * where the assigned_to field matches the provided
     * user ID, allowing for easy retrieval of all activities
     * assigned to a particular user in the system.
     *
     * @param  Builder<Activity> $query The query builder instance.
     * @param  int $userId
     *
     * @return Builder<Activity> The modified query builder instance
     * with the applied filter for assigned user.
     */
    public function scopeAssignedTo(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to activities for a given subject type.
     *
     * This scope filters the query to include only
     * activities where the subject_type field matches
     * the provided fully-qualified class name, allowing
     * for easy retrieval of all activities associated
     * with a specific type of subject model (e.g. Company,
     * Deal, Task, User) in the system.
     * The subject type is determined by the polymorphic
     * relationship defined in the Activity model, and this
     * scope provides a convenient way to filter activities
     * based on the type of their associated subject model.
     *
     * @param  Builder<Activity> $query The query builder instance.
     * @param  string $type A fully-qualified class name (e.g.
     * Company::class).
     *
     * @return Builder<Activity> The modified query builder instance with
     * the applied filter for subject type.
     */
    public function scopeForSubjectType(
        Builder $query,
        string $type
    ): Builder {
        return $query->where('subject_type', $type);
    }

    /**
     * Scope a query to activities for a specific subject instance.
     *
     * This scope filters the query to include only activities
     * where the subject_type field matches the provided
     * fully-qualified class name and the subject_id field
     * matches the provided primary key, allowing for easy
     * retrieval of all activities associated with a specific
     * instance of a subject model (e.g. a specific Company,
     * Deal, Task, or User) in the system. This scope is useful
     * for retrieving activities that are directly related to a
     * particular subject instance, providing a convenient way to
     * filter activities based on both their associated subject
     * type and specific subject instance.
     * The subject type and ID are determined by the polymorphic
     * relationship defined in the Activity model, and this scope
     * provides a convenient way to filter activities based on the
     * specific subject they are associated with.
     *
     * @param  Builder<Activity> $query The query builder instance.
     * @param  string $type A fully-qualified class name.
     * @param  int $id   The subject model's primary key.
     *
     * @return Builder<Activity> The modified query builder instance
     * with the applied filters for subject type and ID.
     */
    public function scopeForSubject(
        Builder $query,
        string $type,
        int $id
    ): Builder {
        return $query->where('subject_type', $type)->where('subject_id', $id);
    }

    /**
     * Scope a query to exclude test records.
     *
     * This scope filters the query to include only activities
     * where the 'is_test' attribute is false, effectively
     * excluding any activities that are marked as test records.
     * This is useful for ensuring that queries return only real
     * activity data, without including any test entries that
     * may have been created for testing or development purposes.
     * By applying this scope, developers can easily filter out
     * test records from their activity queries, improving the
     * accuracy and relevance of the results.
     * The 'is_test' attribute is a boolean field in the
     * Activity model that indicates whether a given activity
     * record is a test entry. When this scope is applied,
     * only activities that are not marked as test records
     * will be included in the query results.
     *
     * @param  Builder<Activity> $query The query builder instance.
     *
     * @return Builder<Activity> The modified query builder instance
     * with the test records excluded.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
