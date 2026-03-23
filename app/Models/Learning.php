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
     * Contants
     */
    public const COMPLETE = 'complete';
    public const INCOMPLETE = 'incomplete';

    protected $fillable = [
        'title',
        'description',
        'date',
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
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_test' => 'boolean',
        'meta' => 'array',
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the questions of the learning.
     *
     * @return HasMany
     */
    public function questions(): HasMany
    {
        return $this->hasMany(LearningQuestion::class);
    }

    /**
     * Get the user that created the learning.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the learning.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the learning.
     *
     * @return BelongsTo
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the learning.
     *
     * @return BelongsTo
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the user that completed the learning.
     *
     * @return BelongsTo
     */
    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The users that are assigned to this learning.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(LearningUser::class)
            ->withPivot([
                'is_complete', 'user_id', 'completed_at', 'is_test', 'meta',
                'created_by', 'updated_by',
            ])
            ->withTimestamps();
    }

    /**
     * Get all of the learning attachments.
     *
     * @return MorphMany
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all of the learning activities.
     *
     * @return MorphMany
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all of the learning tasks.
     *
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all of the learning notes.
     *
     * @return MorphMany
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Scope a query to only include learnings assigned to a given user.
     *
     * @param Builder $query The query builder instance.
     *
     * @param int $userId The ID of the user to filter by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /**
     * Scope a query to only include completed learnings.
     *
     * @param Builder $query The query builder instance.
     *
     * @param int $userId The id of the user.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeCompletedForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId)
                ->wherePivot('is_complete', true);
        });
    }

    /**
     * Scope a query to only include incomplete learnings.
     *
     * @param Builder $query The query builder instance.
     *
     * @param int $userId The id of the user.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeIncompleteForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId)
                ->wherePivot('is_complete', false);
        });
    }

    /**
     * Get the learning title.
     *
     * Applies the test prefix when the learning is marked as a test.
     *
     * @param  string|null  $value  The raw learning title from the database.
     *
     * @return string
     */
    public function getTitleAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
