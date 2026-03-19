<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * Constants
     */
    public const ATTACHABLE_COMPANY = Company::class;
    public const ATTACHABLE_CONTACT = Contact::class;
    public const ATTACHABLE_DEAL = Deal::class;
    public const ATTACHABLE_TASK = Task::class;
    public const ATTACHABLE_USER = User::class;

    public const ATTACHABLE_TYPES = [
        self::ATTACHABLE_COMPANY,
        self::ATTACHABLE_CONTACT,
        self::ATTACHABLE_DEAL,
        self::ATTACHABLE_TASK,
        self::ATTACHABLE_USER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filename',
        'disk',
        'path',
        'uploaded_by',
        'size',
        'mime',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the parent attachable model (contact, deal, etc.).
     *
     * @return MorphTo
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded the attachment.
     *
     * @return BelongsTo
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the size of the attachment in a human-readable format.
     *
     * @return string
     */
    public function getSizeFormattedAttribute(): string
    {
        $size = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($size) - 1) / 3);
        return sprintf(
            '%.2f',
            $size / pow(1024, $factor)
        ) . ' ' . $units[$factor];
    }

    /**
     * Get the MIME type of the attachment.
     *
     * @return string
     */
    public function getMimeTypeAttribute(): string
    {
        return $this->mime;
    }

    /**
     * Get the filename without the extension.
     *
     * @return string
     */
    public function getFilenameWithoutExtensionAttribute(): string
    {
        return pathinfo($this->filename, PATHINFO_FILENAME);
    }

    /**
     * Get the file extension of the attachment.
     *
     * @return string
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    /**
     * Get the user that created the attachment.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the attachment.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the attachment.
     *
     * @return BelongsTo
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the attachment.
     *
     * @return BelongsTo
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all of the attactment activities.
     *
     * @return MorphMany
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all of the attactment tasks.
     *
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all of the attachment notes.
     *
     * @return MorphMany
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the attachment name.
     *
     * Applies the test prefix when the attachment is marked as a test.
     *
     * @return string
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Delete the attachment file from storage when the model is deleted.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function (Attachment $attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        });
    }
}
