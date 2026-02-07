<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Learning extends Model
{
    /**
     * @use HasFactory<\Database\Factories\LearningFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'date',
        'created_by',
        'updated_by',
        'deleted_by',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'meta' => 'array',
    ];

    /**
     * Get the user that created the learning.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the learning.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the learning.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that completed the learning.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * The users that are assigned to this learning.     
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['is_completed', 'completed_by', 'completed_at'])
            ->withTimestamps();
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
     * @return Builder The modified query builder instance.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope a query to only include incomplete learnings.
     *
     * @param Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->where('is_completed', false);
    }
}
