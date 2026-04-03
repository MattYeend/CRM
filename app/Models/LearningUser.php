<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model representing the many-to-many relationship between learnings
 * and users.
 *
 * Tracks each user's completion state and timestamp for an assigned learning,
 * along with the standard audit tracking columns.
 *
 * Relationships defined in this model include:
 * - learning(): The learning that this pivot entry belongs to.
 * - user(): The user that this pivot entry belongs to.
 * - creator(): The user that created this pivot entry.
 * - updater(): The user that last updated this pivot entry.
 * - completer(): The user that completed this learning (if applicable).
 * Example usage of relationships:
 * ```php
 * $learningUser = LearningUser::find(1);
 * $learning = $learningUser->learning; // Get the associated learning
 * $user = $learningUser->user; // Get the associated user
 * $creator = $learningUser->creator; // Get the user that created this pivot entry
 * $updater = $learningUser->updater; // Get the user that last updated this pivot entry
 * $completer = $learningUser->completer; // Get the user that completed this learning (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getStatusAttribute(): Returns the status of the learning for the user, which can be 'complete' or 'incomplete'.
 * - getScoreAttribute(): Returns the score of the learning for the user, if applicable.
 * Example usage of accessors:
 * ```php
 * $learningUser = LearningUser::find(1);
 * $status = $learningUser->status; // e.g. "complete" or "incomplete"
 * $score = $learningUser->score; // e.g. 85 or null if not applicable
 * ```
 *
 * Query scopes include:
 * - scopeCompletedBetween($query, $startDate, $endDate): Filter learnings completed within a given date range.
 * - scopeCompletedForUser($query, $userId): Filter to only include completed learnings for a given user.
 * - scopeIncompleteForUser($query, $userId): Filter to only include incomplete learnings for a given user.
 * - scopeForUser($query, $userId): Filter to only include learnings for a given user.
 * - scopeRealForUser($query, $userId): Filter to only include non-test learnings for a given user.
 * Example usage of query scopes:
 * ```php
 * $completedLearnings = LearningUser::completedBetween('2024-01-01', '2024-01-31')->get(); // Get learnings completed in January 2024
 * $completedForUser = LearningUser::completedForUser($userId)->get(); // Get completed learnings for a specific user
 * $incompleteForUser = LearningUser::incompleteForUser($userId)->get(); // Get incomplete learnings for a specific user
 * $learningsForUser = LearningUser::forUser($userId)->get(); // Get all learnings for a specific user
 * $realLearningsForUser = LearningUser::realForUser($userId)->get(); // Get non-test learnings for a specific user
 * ```
 */
class LearningUser extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'learning_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'learning_id',
        'user_id',
        'is_complete',
        'completed_at',
        'score',
        'is_test',
        'meta',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'score' => 'integer',
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the learning that this pivot entry belongs to.
     *
     * @return BelongsTo<Learning,LearningUser>
     */
    public function learning(): BelongsTo
    {
        return $this->belongsTo(Learning::class);
    }

    /**
     * Get the user that this pivot entry belongs to.
     *
     * @return BelongsTo<User,LearningUser>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that created this pivot entry.
     *
     * @return BelongsTo<User,LearningUser>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated this pivot entry.
     *
     * @return BelongsTo<User,LearningUser>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that completed this learning (if applicable).
     *
     * @return BelongsTo<User,LearningUser>
     */
    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the user that completed this learning (if applicable).
     *
     * @return BelongsTo<User,LearningUser>
     */
    public function getCompleterAttribute(): ?User
    {
        return $this->completer()->first();
    }

    /**
     * Get the status of the learning for the user.
     *
     * @return string The status of the learning for the user, which can be 'complete', or 'incomplete'.
     */
    public function getStatusAttribute(): string
    {
        return $this->is_complete ? 'complete' : 'incomplete';
    }

    /**
     * Get the score of the learning for the user, if applicable.
     *
     * @return int|null The score of the learning for the user, or null if not applicable.
     */
    public function getScoreAttribute(): ?int
    {
        return $this->attributes['score'] ?? null;
    }

    /**
     * Scope a query to only include learnings completed within a given date range.
     *
     * @param Builder $query The query builder instance.
     * @param string|null $startDate The start date of the range (inclusive).
     * @param string|null $endDate The end date of the range (inclusive).
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeCompletedBetween($query, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate) {
            $query->where('completed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('completed_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope a query to only include completed learnings for a given user.
     *
     * @param Builder $query The query builder instance.
     * @param int $userId The ID of the user to filter by.
     * @return Builder The modified query builder instance.
     */
    public function scopeCompletedForUser($query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
                     ->where('is_complete', true);
    }

    /**
     * Scope a query to only include incomplete learnings for a given user.
     *
     * @param Builder $query The query builder instance.
     * @param int $userId The ID of the user to filter by.
     * @return Builder The modified query builder instance.
     */
    public function scopeIncompleteForUser($query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
                     ->where('is_complete', false);
    }

    /**
     * Scope a query to only include learnings for a given user.
     *
     * @param Builder $query The query builder instance.
     * @param int $userId The ID of the user to filter by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeForUser($query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include non-test learnings for a given user.
     *
     * @param Builder $query The query builder instance.
     * @param int $userId The ID of the user to filter by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeRealForUser($query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
                     ->where('is_test', false);
    }
}
