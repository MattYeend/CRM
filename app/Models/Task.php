<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public const TASKABLE_COMPANY = Company::class;
    public const TASKABLE_CONTACT = Contact::class;
    public const TASKABLE_DEAL = Deal::class;
    public const TASKABLE_TASK = Task::class;
    public const TASKABLE_USER = User::class;

    public const TASKABLE_TYPES = [
        self::TASKABLE_COMPANY,
        self::TASKABLE_CONTACT,
        self::TASKABLE_DEAL,
        self::TASKABLE_TASK,
        self::TASKABLE_USER,
    ];

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
     * @var array<string, string>
     */
    protected $casts = [
        'is_test' => 'boolean',
        'meta' => 'array',
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the parent taskable model (deal, contact, company, etc.).
     *
     * @return MorphTo
     */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user assigned to the task.
     *
     * @return BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created the task.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the task.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the task.
     *
     * @return BelongsTo
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the task.
     *
     * @return BelongsTo
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all of the tasks attachments.
     *
     * @return MorphMany
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all of the tasks activities.
     *
     * @return MorphMany
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all of the tasks tasks.
     *
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all of the tasks notes.
     *
     * @return MorphMany
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Scope a query to only include tasks of a given status.
     *
     * @param Builder $query
     *
     * @param string $status
     *
     * @return Builder
     */
    public function scopeStatus($query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include tasks of a given priority.
     *
     * @param Builder $query
     *
     * @param string $priority
     *
     * @return Builder
     */
    public function scopePriority($query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include tasks due before a given date.
     *
     * @param Builder $query
     *
     * @param \DateTime|string $date
     *
     * @return Builder
     */
    public function scopeDueBefore($query, $date): Builder
    {
        return $query->where('due_at', '<', $date);
    }

    /**
     * Scope a query to only include tasks due after a given date.
     *
     * @param Builder $query
     *
     * @param \DateTime|string $date
     *
     * @return Builder
     */
    public function scopeDueAfter($query, $date): Builder
    {
        return $query->where('due_at', '>', $date);
    }

    /**
     * Scope a query to only include tasks assigned to a given user.
     *
     * @param Builder $query
     *
     * @param int $userId
     *
     * @return Builder
     */
    public function scopeAssignedTo($query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }
}
