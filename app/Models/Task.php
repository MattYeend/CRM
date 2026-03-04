<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'taskable_type',
        'taskable_id',
        'priority',
        'status',
        'due_at',
        'meta',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'array',
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the parent taskable model (deal, contact, company, etc.).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user assigned to the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope a query to only include tasks of a given status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @param string $status
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include tasks of a given priority.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @param string $priority
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePriority($query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include tasks due before a given date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @param \DateTime|string $date
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueBefore($query, $date): Builder
    {
        return $query->where('due_at', '<', $date);
    }

    /**
     * Scope a query to only include tasks due after a given date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @param \DateTime|string $date
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueAfter($query, $date): Builder
    {
        return $query->where('due_at', '>', $date);
    }

    /**
     * Scope a query to only include tasks assigned to a given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedTo($query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }
}
