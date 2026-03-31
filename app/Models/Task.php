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
 * Represents a task within the system.
 *
 * Tasks track actionable items assigned to users and may be associated
 * with various entities (e.g. deals, companies, users, or other tasks)
 * via a polymorphic relationship.
 *
 * Tasks support lifecycle states (pending, completed, cancelled),
 * priority levels, due dates, and audit tracking. They may also be
 * marked as test records, in which case certain attributes (e.g. title)
 * are automatically prefixed.
 */
class Task extends Model
{
    /**
     * @use HasFactory<\Database\Factories\TaskFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * Task is pending.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Task has been completed.
     */
    public const STATUS_COMPLETED = 'completed';

    /**
     * Task has been cancelled.
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Low priority task.
     */
    public const PRIORITY_LOW = 'low';

    /**
     * Medium priority task.
     */
    public const PRIORITY_MEDIUM = 'medium';

    /**
     * High priority task.
     */
    public const PRIORITY_HIGH = 'high';

    /**
     * Taskable type: Company.
     */
    public const TASKABLE_COMPANY = Company::class;

    /**
     * Taskable type: Deal.
     */
    public const TASKABLE_DEAL = Deal::class;

    /**
     * Taskable type: Task.
     */
    public const TASKABLE_TASK = Task::class;

    /**
     * Taskable type: User.
     */
    public const TASKABLE_USER = User::class;

    /**
     * All valid taskable types.
     *
     * Suitable for validation and filtering.
     */
    public const TASKABLE_TYPES = [
        self::TASKABLE_COMPANY,
        self::TASKABLE_DEAL,
        self::TASKABLE_TASK,
        self::TASKABLE_USER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
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
     * @var array<string,string>
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
     * Get the parent taskable model (e.g. Company, Deal, Task, or User).
     *
     * @return MorphTo<Model,Task>
     */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user assigned to the task.
     *
     * @return BelongsTo<User,Task>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user that created the task.
     *
     * @return BelongsTo<User,Task>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the task.
     *
     * @return BelongsTo<User,Task>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the task.
     *
     * @return BelongsTo<User,Task>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the task.
     *
     * @return BelongsTo<User,Task>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the task.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the task.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all notes associated with the task.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Scope a query to tasks with a given status.
     *
     * @param  Builder<Task> $query The query builder instance.
     * @param  string $status The task status.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to tasks with a given priority.
     *
     * @param  Builder<Task> $query The query builder instance.
     * @param  string $priority The task priority.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopePriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to tasks due before a given date.
     *
     * @param  Builder<Task> $query The query builder instance.
     * @param  \DateTimeInterface|string $date The cutoff date.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeDueBefore(Builder $query, $date): Builder
    {
        return $query->where('due_at', '<', $date);
    }

    /**
     * Scope a query to tasks due after a given date.
     *
     * @param  Builder<Task> $query The query builder instance.
     * @param  \DateTimeInterface|string $date The cutoff date.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeDueAfter(Builder $query, $date): Builder
    {
        return $query->where('due_at', '>', $date);
    }

    /**
     * Scope a query to tasks assigned to a given user.
     *
     * @param  Builder<Task> $query The query builder instance.
     * @param  int $userId The user ID.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Get the formatted task title.
     *
     * Applies a test prefix when the task is marked as a test record.
     *
     * @param  string|null $value The raw task title from the database.
     *
     * @return string The formatted task title.
     */
    public function getTitleAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
