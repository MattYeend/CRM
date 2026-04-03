<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a learning resource that can be assigned to users.
 *
 * Each learning may carry a set of questions with answers. Completion state
 * is tracked per user via the LearningUser pivot. Query scopes are provided
 * to filter learnings by assignment and completion status for a given user.
 *
 * Relationships defined in this model include:
 * - questions(): One-to-many relationship to LearningQuestion records
 *      associated with this learning.
 * - users(): Many-to-many relationship to User records assigned to this
 *      learning, with pivot data for completion state and timestamps.
 * - attachments(): Polymorphic one-to-many relationship to Attachment records
 *      associated with this learning.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with this learning.
 * - tasks(): Polymorphic one-to-many relationship to Task records associated
 *      with this learning.
 * - notes(): Polymorphic one-to-many relationship to Note records associated
 *      with this learning.
 * Example usage of relationships:
 * ```php
 * $learning = Learning::find(1);
 * $questions = $learning->questions; // Get all questions for this learning
 * $users = $learning->users; // Get all users assigned to this learning
 * $attachments = $learning->attachments; // Get all attachments for this
 *  learning
 * $activities = $learning->activities; // Get all activities for this learning
 * $tasks = $learning->tasks; // Get all tasks for this learning
 * $notes = $learning->notes; // Get all notes for this learning
 * ```
 *
 * Accessor methods include:
 * - getTitleAttribute(): Returns the learning title, applying a test prefix
 *      if the learning is marked as a test.
 * - getDescriptionAttribute(): Returns the learning description, defaulting to
 *      an empty string if null.
 * - getMetaTitleAttribute(): Returns the meta title from the meta JSON,
 *      defaulting to an empty string if not set.
 * - getMetaDescriptionAttribute(): Returns the meta description from the
 *      meta JSON, defaulting to an empty string if not set.
 * - getMetaKeywordsAttribute(): Returns the meta keywords from the meta JSON,
 *      defaulting to an empty string if not set.
 * - getMetaAuthorAttribute(): Returns the meta author from the meta JSON,
 *      defaulting to an empty string if not set.
 * Example usage of accessors:
 * ```php
 * $learning = Learning::find(1);
 * $title = $learning->title; // Get the learning title with test prefix if
 *  applicable
 * $description = $learning->description; // Get the learning description
 * $metaTitle = $learning->meta_title; // Get the meta title from the meta JSON
 * $metaDescription = $learning->meta_description; // Get the meta description
 *  from the meta JSON
 * $metaKeywords = $learning->meta_keywords; // Get the meta keywords from the
 *  meta JSON
 * $metaAuthor = $learning->meta_author; // Get the meta author from the meta
 *  JSON
 * ```
 *
 * Query scopes include:
 * - scopeForUser($query, $userId): Filter the query to only include learnings
 *      assigned to a given user.
 * - scopeCompletedForUser($query, $userId): Filter the query to only include
 *      learnings completed by a given user.
 * - scopeIncompleteForUser($query, $userId): Filter the query to only include
 *      learnings not yet completed by a given user.
 * - scopeReal($query): Filter the query to only include non-test learnings.
 * Example usage of query scopes:
 * ```php
 * $assigned = Learning::forUser($userId)->get(); // Learnings assigned to
 *  a user
 * $completed = Learning::completedForUser($userId)->get(); // Learnings
 *  completed by a user
 * $incomplete = Learning::incompleteForUser($userId)->get(); // Learnings
 *  not yet completed by a user
 * $real = Learning::real()->get(); // Exclude test learnings
 * ```
 */
class Learning extends Model
{
    /**
     * @use HasFactory<\Database\Factories\LearningFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * Represents a learning that has been completed.
     */
    public const COMPLETE = 'complete';

    /**
     * Represents a learning that is yet to be completed.
     */
    public const INCOMPLETE = 'incomplete';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'title',
        'description',
        'date',
        'pass_score',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
        'is_test',
        'meta',
        'created_at',
        'updated_at',
        'deleted_at',
        'restored_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'pass_score' => 'integer',
        'is_test' => 'boolean',
        'meta' => 'array',
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the questions for the learning.
     *
     * @return HasMany<LearningQuestion>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(LearningQuestion::class);
    }

    /**
     * Get the user that created the learning.
     *
     * @return BelongsTo<User,Learning>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the learning.
     *
     * @return BelongsTo<User,Learning>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the learning.
     *
     * @return BelongsTo<User,Learning>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the learning.
     *
     * @return BelongsTo<User,Learning>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the user that completed the learning.
     *
     * @return BelongsTo<User,Learning>
     */
    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the users assigned to this learning.
     *
     * Pivot data includes completion state, timestamp, and audit fields
     * via the LearningUser pivot model.
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(LearningUser::class)
            ->withPivot([
                'is_complete', 'user_id', 'completed_at', 'is_test', 'meta',
                'created_by', 'updated_by', 'score',
            ])
            ->withTimestamps();
    }

    /**
     * Get all attachments associated with the learning.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the learning.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the learning.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the learning.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the learning title, applying the test prefix when marked as a test.
     *
     * @param  string|null $value The raw learning title from the database.
     *
     * @return string
     */
    public function getTitleAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Get the learning description.
     *
     * @param  string|null $value The raw learning description from the
     * database.
     *
     * @return string
     */
    public function getDescriptionAttribute($value): string
    {
        return $value ?? '';
    }

    /**
     * Get the learning meta title.
     *
     * @param  string|null $value The raw learning meta title from the
     * database.
     *
     * @return string
     */
    public function getMetaTitleAttribute($value): string
    {
        return $value ?? '';
    }

    /**
     * Get the learning meta description.
     *
     * @param  string|null $value The raw learning meta description from the
     * database.
     *
     * @return string
     */
    public function getMetaDescriptionAttribute($value): string
    {
        return $value ?? '';
    }

    /**
     * Get the learning meta keywords.
     *
     * @param  string|null $value The raw learning meta keywords from the
     * database.
     *
     * @return string
     */
    public function getMetaKeywordsAttribute($value): string
    {
        return $value ?? '';
    }

    /**
     * Get the learning meta author.
     *
     * @param  string|null $value The raw learning meta author from the
     * database.
     *
     * @return string
     */
    public function getMetaAuthorAttribute($value): string
    {
        return $value ?? '';
    }

    /**
     * Scope a query to only include learnings assigned to a given user.
     *
     * @param  Builder $query The query builder instance.
     *
     * @param  int $userId The ID of the user to filter by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeForUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /**
     * Scope a query to only include learnings completed by a given user.
     *
     * @param  Builder $query The query builder instance.
     *
     * @param  int $userId The ID of the user to filter by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeCompletedForUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId)
                ->wherePivot('is_complete', true);
        });
    }

    /**
     * Scope a query to only include learnings not yet completed
     * by a given user.
     *
     * @param  Builder $query The query builder instance.
     *
     * @param  int $userId The ID of the user to filter by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeIncompleteForUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId)
                ->wherePivot('is_complete', false);
        });
    }

    /**
     * Scope a query to only include non-test learnings.
     *
     * @param  Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
