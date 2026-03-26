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
     * Represents a task that is pending.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Represents a task that has been complete.
     */
    public const STATUS_COMPLETED = 'completed';

    /**
     * Represents a task that has been cancelled.
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Represents a task that is a low priority.
     */
    public const PRIORITY_LOW = 'low';

    /**
     * Represents a task that is a medium priority.
     */
    public const PRIORITY_MEDIUM = 'medium';

    /**
     * Represents a task that is a high priority.
     */
    public const PRIORITY_HIGH = 'high';

    /**
     * The fully-qualified class name used as the taskabl type for Company attachments.
     */
    public const TASKABLE_COMPANY = Company::class;

    /**
     * The fully-qualified class name used as the taskable type for Deal attachments.
     */
    public const TASKABLE_DEAL = Deal::class;

    /**
     * The fully-qualified class name used as the taskable type for Task attachments.
     */
    public const TASKABLE_TASK = Task::class;

    /**
     * The fully-qualified class name used as the taskable type for User attachments.
     */
    public const TASKABLE_USER = User::class;

    /**
     * All valid taskable types that an attachment can be associated with.
     *
     * @var array<int, class-string<Model>>
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
     * Get the parent taskable model (deal, , company, etc.).
     *
     * @return MorphTo<
     */
    /**
     * Get the polymorphic attachable model this taskable belongs to.
     *
     * The taskable may be a Company, Deal, Task, or User as defined in TASKABLE_TYPES.
     *
     * @return MorphTo<Model,Taskable>
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
     * Get the user who created the task.
     *
     * @return BelongsTo<User,Task>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the task.
     *
     * @return BelongsTo<User,Task>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the task.
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
     * Get all of the tasks attachments.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all of the tasks activities.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all of the tasks notes.
     *
     * @return MorphMany<Note>
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

    /**
     * Get the task title, applies the test prefix when the task is marked as a test.
     *
     * @param  string|null  $value  The raw task title from the database.
     *
     * @return string
     */
    public function getTitleAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
