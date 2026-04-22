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
 *
 * Relationships defined in this model include:
 * - taskable(): Polymorphic belongs-to relationship to the parent entity
 *      (Company, Deal, Task, or User) the task is associated with.
 * - assignee(): Belongs-to relationship to the User assigned to the task.
 * - creator(): Belongs-to relationship to the User who created the task.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      task.
 * - deleter(): Belongs-to relationship to the User who deleted the task
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the task
 *      (if soft-deleted).
 * - attachments(): Polymorphic one-to-many relationship to Attachment
 *      records associated with the task.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the task.
 * - notes(): Polymorphic one-to-many relationship to Note records
 *      associated with the task.
 * Example usage of relationships:
 * ```php
 * $task = Task::find(1);
 * $parent = $task->taskable; // Get the parent entity (Company, Deal, etc.)
 * $assignee = $task->assignee; // Get the assigned user
 * $creator = $task->creator; // Get the user that created the task
 * $notes = $task->notes; // Get all notes for the task
 * ```
 *
 * Accessor methods include:
 * - getTitleAttribute(): Returns the task title, applying a test prefix
 *      if the task is marked as a test record.
 * - getIsOverdueAttribute(): Returns a boolean indicating whether the
 *      task is past its due date and not yet completed or cancelled.
 * - getIsPendingAttribute(): Returns a boolean indicating whether the
 *      task has a pending status.
 * - getIsCompletedAttribute(): Returns a boolean indicating whether the
 *      task has a completed status.
 * - getIsCancelledAttribute(): Returns a boolean indicating whether the
 *      task has a cancelled status.
 * Example usage of accessors:
 * ```php
 * $task = Task::find(1);
 * $title = $task->title; // Get the title with test prefix if applicable
 * $isOverdue = $task->is_overdue; // Check if the task is past its due date
 * $isPending = $task->is_pending; // Check if the task is pending
 * $isCompleted = $task->is_completed; // Check if the task is completed
 * ```
 *
 * Query scopes include:
 * - scopeStatus($query, $status): Filter the query to only include tasks
 *      with a given status.
 * - scopePriority($query, $priority): Filter the query to only include
 *      tasks with a given priority.
 * - scopeDueBefore($query, $date): Filter the query to only include tasks
 *      due before a given date.
 * - scopeDueAfter($query, $date): Filter the query to only include tasks
 *      due after a given date.
 * - scopeAssignedTo($query, $userId): Filter the query to only include
 *      tasks assigned to a given user.
 * - scopePending($query): Filter the query to only include pending tasks.
 * - scopeCompleted($query): Filter the query to only include completed tasks.
 * - scopeCancelled($query): Filter the query to only include cancelled tasks.
 * - scopeOverdue($query): Filter the query to only include tasks that are
 *      past their due date and not yet completed or cancelled.
 * - scopeReal($query): Filter the query to only include non-test tasks.
 * - scopeSearchTitle($query, $term): Filter the query to tasks with titles
 * Example usage of query scopes:
 * ```php
 * $pending = Task::pending()->get(); // Get all pending tasks
 * $overdue = Task::overdue()->get(); // Get all overdue tasks
 * $highPrio = Task::priority('high')->get(); // Get high-priority tasks
 * $myTasks = Task::assignedTo($userId)->get(); // Get tasks for a user
 * $realTasks = Task::real()->get(); // Exclude test records
 * $searchResults = Task::searchTitle('call')->get(); // Tasks with 'call'
 * in the title
 * ```
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
     * Get the formatted task title.
     *
     * Applies a test prefix when the task is marked as a test record.
     *
     * @param  string|null  $value  The raw task title from the database.
     *
     * @return string The formatted task title.
     */
    public function getTitleAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Determine whether the task is overdue.
     *
     * A task is considered overdue when it has a due date that is in the
     * past and its status is neither completed nor cancelled. Tasks without
     * a due date are never considered overdue.
     *
     * @return bool
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isPast()
            && ! in_array($this->status, [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
            ], true);
    }

    /**
     * Determine whether the task has a pending status.
     *
     * @return bool
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Determine whether the task has a completed status.
     *
     * @return bool
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Determine whether the task has a cancelled status.
     *
     * @return bool
     */
    public function getIsCancelledAttribute(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
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
     * @param  Builder<Task>  $query     The query builder instance.
     * @param  string         $priority  The task priority to filter by.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopePriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include pending tasks.
     *
     * @param  Builder<Task> $query The query builder instance.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include completed tasks.
     *
     * @param  Builder<Task> $query The query builder instance.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include cancelled tasks.
     *
     * @param  Builder<Task>  $query  The query builder instance.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope a query to only include overdue tasks.
     *
     * A task is overdue when its due date is in the past and its status
     * is neither completed nor cancelled.
     *
     * @param  Builder<Task> $query The query builder instance.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
            ]);
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
     * @param  Builder<Task> $query  The query builder instance.
     * @param  \DateTimeInterface|string $date   The cutoff date.
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
     * @param  int $userId The ID of the user to filter by.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to only include task of a given taskable type.
     *
     * @param  Builder<Task> $query The query builder instance.
     * @param  string $taskableType The taskable type to filter by, as a class
     * basename (e.g. "Company" instead of "App\Models\Company").
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeOfTaskableType(
        Builder $query,
        string $taskableType
    ): Builder {
        $taskableTypeClass = collect(self::TASKABLE_TYPES)
            ->first(fn ($type) => class_basename($type) === $taskableType);

        if (! $taskableTypeClass) {
            throw new \InvalidArgumentException(
                "Invalid taskable type: {$taskableType}"
            );
        }

        return $query->where('taskable_type', $taskableTypeClass);
    }

    /**
     * Scope a query to only include tasks associated with a given
     * taskable model.
     *
     * @param  Builder<Task> $query The query builder instance.
     * @param  Model $taskable The taskable model to filter by.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeForTaskable(Builder $query, Model $taskable): Builder
    {
        return $query->where('taskable_type', $taskable->getMorphClass())
            ->where('taskable_id', $taskable->id);
    }

    /**
     * Scope a query to only include non-test tasks.
     *
     * Filters out any task records where the 'is_test' flag is true,
     * ensuring that queries return only real task records.
     *
     * @param  Builder<Task>  $query The query builder instance.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * Scope a query to search for tasks with a title containing a given
     * keyword.
     *
     * Wraps the conditions in a grouped where clause to ensure correct
     * boolean precedence when combined with other scopes.
     *
     * @param  Builder<Task> $query The query builder instance.
     * @param  string $term The term to search for in task titles.
     *
     * @return Builder<Task> The modified query builder instance.
     */
    public function scopeSearchTitle(
        Builder $query,
        string $term
    ): Builder {
        $like = "%{$term}%";

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', '%' . $like . '%');
        });
    }
}
