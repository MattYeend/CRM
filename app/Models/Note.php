<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
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
     * The fully-qualified class name used as the notable type for Company activities.
     */
    public const NOTABLE_COMPANY = Company::class;

    /**
     * The fully-qualified class name used as the notable type for Deal activities.
     */
    public const NOTABLE_DEAL = Deal::class;

    /**
     * The fully-qualified class name used as the subject type for Task activities.
     */
    public const NOTABLE_TASK = Task::class;

    /**
     * The fully-qualified class name used as the subject type for User activities.
     */
    public const NOTABLE_USER = User::class;

    /**
     * All valid notable types that an activity can be associated with.
     *
     * @var array<int, class-string<Model>>
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
     * @var array<int, string>
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
     * @var array<string, string>
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
     * The note may be a Company, Deal, Task, or User as defined in NOTABLE_TYPES.
     *
     * @return MorphTo<Notable>
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
     * Get all of the note attachments.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all of the note activities.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all of the note tasks.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get the note type, applies the test prefix when the note is marked as a test.
     *
     * @param  string|null  $value  The raw note type from the database.
     *
     * @return string
     */
    public function getTypeAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
