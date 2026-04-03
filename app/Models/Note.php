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
 * Represents a note attached to a polymorphic notable model.
 *
 * Notes may be associated with a Company, Deal, Task, or User via the notable
 * relationship, and are optionally attributed to a user via the user
 * relationship.
 *
 * Relationships included in this model include:
 * - notable: The polymorphic parent model this note is attached to (Company,
 *      Deal, Task, or User).
 * - user: The user that the note is attributed to (optional).
 * - creator: The user that created the note (optional).
 * - updater: The user that last updated the note (optional).
 * - deleter: The user that deleted the note (if soft-deleted, optional).
 * - restorer: The user that restored the note (if soft-deleted, optional).
 * - attachments: The attachments associated with the note.
 * - activities: The activities associated with the note.
 * - tasks: The tasks associated with the note.
 * Example usage of relationships:
 * ```php
 * $note = Note::find(1);
 * $notable = $note->notable; // Get the notable model this note is attached to
 * $user = $note->user; // Get the user this note is attributed to (if any)
 * $creator = $note->creator; // Get the user that created this note (if any)
 * $updater = $note->updater; // Get the user that last updated this note (if any)
 * $deleter = $note->deleter; // Get the user that deleted this note (if applicable)
 * $restorer = $note->restorer; // Get the user that restored this note (if applicable)
 * $attachments = $note->attachments; // Get the attachments associated with this note
 * $activities = $note->activities; // Get the activities associated with this note
 * $tasks = $note->tasks; // Get the tasks associated with this note
 * ```
 *
 * Accessor methods include:
 * - getTypeAttribute(): Get the note body with the test prefix applied if marked as a test.
 * Example usage of accessors:
 * ```php
 * $note = Note::find(1);
 * $type = $note->type; // Get the note body with test prefix if applicable
 * ```
 *
 * Query scopes include:
 * - scopeOfNotableType($query, $notableType): Filter notes by notable type using a class basename.
 * - scopeForNotable($query, $notable): Filter notes by a specific notable model instance.
 * - scopeReal($query): Filter notes to only include non-test notes.
 * Example usage of query scopes:
 * ```php
 * $companyNotes = Note::ofNotableType('Company')->get(); // Get all notes for the Company notable type
 * $notableNotes = Note::forNotable($notable)->get(); // Get all notes for a specific notable model instance
 * $realNotes = Note::real()->get(); // Get all non-test notes
 * ```
 */
class Note extends Model
{
    /**
     * @use HasFactory<\Database\Factories\NoteFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * The fully-qualified class name used as the notable type for Company
     * notes.
     */
    public const NOTABLE_COMPANY = Company::class;

    /**
     * The fully-qualified class name used as the notable type for Deal notes.
     */
    public const NOTABLE_DEAL = Deal::class;

    /**
     * The fully-qualified class name used as the notable type for Task notes.
     */
    public const NOTABLE_TASK = Task::class;

    /**
     * The fully-qualified class name used as the notable type for User notes.
     */
    public const NOTABLE_USER = User::class;

    /**
     * All valid notable types that a note can be associated with.
     */
    public const NOTABLE_TYPES = [
        self::NOTABLE_COMPANY,
        self::NOTABLE_DEAL,
        self::NOTABLE_TASK,
        self::NOTABLE_USER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'body',
        'user_id',
        'is_test',
        'meta',
        'notable_type',
        'notable_id',
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
     * Get the polymorphic notable model this note belongs to.
     *
     * The note may be a Company, Deal, Task, or User as defined
     * in NOTABLE_TYPES.
     *
     * @return MorphTo<Model,Note>
     */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the note.
     *
     * @return BelongsTo<User,Note>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that created the note.
     *
     * @return BelongsTo<User,Note>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the note.
     *
     * @return BelongsTo<User,Note>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the note.
     *
     * @return BelongsTo<User,Note>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the note.
     *
     * @return BelongsTo<User,Note>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the note.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the note.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the note.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get the note body, applying the test prefix when marked as a test.
     *
     * @param  string|null  $value  The raw note body from the database.
     *
     * @return string
     */
    public function getTypeAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Scope a query to only include notes of a given notable type.
     *
     * @param  Builder<Note> $query The query builder instance.
     * @param  string $notableType The notable type to filter by, as a class
     * basename (e.g. "Company" instead of "App\Models\Company").
     *
     * @return Builder<Note> The modified query builder instance.
     */
    public function scopeOfNotableType(Builder $query, string $notableType): Builder
    {
        $notableTypeClass = collect(self::NOTABLE_TYPES)
            ->first(fn ($type) => class_basename($type) === $notableType);

        if (! $notableTypeClass) {
            throw new \InvalidArgumentException("Invalid notable type: {$notableType}");
        }

        return $query->where('notable_type', $notableTypeClass);
    }

    /**
     * Scope a query to only include notes associated with a given notable model.
     *
     * @param  Builder<Note> $query The query builder instance.
     * @param  Model $notable The notable model to filter by.
     *
     * @return Builder<Note> The modified query builder instance.
     */
    public function scopeForNotable(Builder $query, Model $notable): Builder
    {
        return $query->where('notable_type', get_class($notable))
            ->where('notable_id', $notable->id);
    }

    /** 
     * Scope a query to only include non-test notes.
     *
     * @param  Builder<Note> $query The query builder instance.
     *
     * @return Builder<Note> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
