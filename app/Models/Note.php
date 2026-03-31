<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
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
}
