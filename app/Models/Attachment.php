<?php

namespace App\Models;

use App\Services\Attachments\AttachmentFileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Represents a file attachment uploaded against a polymorphic parent model.
 *
 * Attachments may belong to a Company, Deal, Task, or User via the attachable
 * relationship. The underlying file is stored on a configurable disk and is
 * automatically removed from storage when the model is deleted.
 *
 * Relationships defined in this model include:
 * - attachable(): The polymorphic parent model this attachment
 *      belongs to.
 * - uploader(): The user who uploaded the attachment.
 * - creator(): The user that created the attachment record.
 * - updater(): The user that last updated the attachment record.
 * - deleter(): The user that deleted the attachment record.
 * - restorer(): The user that restored the attachment record.
 * - activities(): All activities associated with the attachment.
 * - tasks(): All tasks associated with the attachment.
 * - notes(): All notes associated with the attachment.
 * Example usage of relationships:
 * ```php
 * // Get the attachable parent model of an attachment
 * $attachment = Attachment::find(1);
 * $attachable = $attachment->attachable;
 * // Get the uploader of an attachment
 * $uploader = $attachment->uploader;
 * // Get the creator of an attachment
 * $creator = $attachment->creator;
 * // Get the updater of an attachment
 * $updater = $attachment->updater;
 * // Get the deleter of an attachment
 * $deleter = $attachment->deleter;
 * // Get the restorer of an attachment
 * $restorer = $attachment->restorer;
 * // Get all activities associated with an attachment
 * $activities = $attachment->activities;
 * // Get all tasks associated with an attachment
 * $tasks = $attachment->tasks;
 * // Get all notes associated with an attachment
 * $notes = $attachment->notes;
 * // Get all attachments for a specific company
 * $company = Company::find(1);
 * $attachments = $company->attachments;
 * // Get all attachments for a specific deal
 * $deal = Deal::find(1);
 * $attachments = $deal->attachments;
 * // Get all attachments for a specific task
 * $task = Task::find(1);
 * $attachments = $task->attachments;
 * // Get all attachments for a specific user
 * $user = User::find(1);
 * $attachments = $user->attachments;
 * ```
 *
 * Accessor methods include:
 * - size_formatted(): Get the size of the attachment in a human-readable
 *      format.
 * - mime_type(): Get the MIME type of the attachment.
 * - filename_without_extension(): Get the filename without the extension.
 * - file_extension(): Get the file extension of the attachment.
 * - name(): Get the attachment name, applying the test prefix when marked
 *      as a test.
 * - download_url(): Get the authenticated download URL for the attachment.
 *
 * Example usage of accessors:
 * ```php
 * // Get the size of an attachment in a human-readable format
 * $attachment = Attachment::find(1);
 * $sizeFormatted = $attachment->size_formatted;
 * // Get the MIME type of an attachment
 * $mimeType = $attachment->mime_type;
 * // Get the filename without extension
 * $filenameWithoutExtension = $attachment->filename_without_extension;
 * // Get the file extension of an attachment
 * $fileExtension = $attachment->file_extension;
 * // Get the name of an attachment, applying test prefix if it's a test record
 * $name = $attachment->name;
 * $downloadUrl = $attachment->download_url;
 * ```
 * Query scopes include:
 * - real(): Scope a query to exclude test records.
 * - attachableType(): Scope a query to a specific attachable type.
 * - attachableId(): Scope a query to a specific attachable ID.
 * Example usage of query scopes:
 * ```php
 * // Get all real attachments for a specific deal
 * $deal = Deal::find(1);
 * $realAttachments = $deal->attachments()->real()->get();
 * // Get all attachments of a specific type and ID
 * $attachments = Attachment::attachableType(Attachment::ATTACHABLE_TASK)
 *  ->attachableId(5)
 * ->get();
 * // Get all real attachments for a specific user
 * $user = User::find(1);
 * $realAttachments = $user->attachments()->real()->get();
 * // Get all attachments for a specific task
 * $task = Task::find(1);
 * $attachments = $task->attachments;
 * // Get all attachments for a specific user
 * $user = User::find(1);
 * $attachments = $user->attachments;
 * // Get all attachments for a specific deal
 * $deal = Deal::find(1);
 * $attachments = $deal->attachments;
 * ```
 */
class Attachment extends Model
{
    /**
     * @use HasFactory<\Database\Factories\AttachmentFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * The fully-qualified class name used as the attachable type for Company
     * attachments.
     */
    public const ATTACHABLE_COMPANY = Company::class;

    /**
     * The fully-qualified class name used as the attachable type for Deal
     * attachments.
     */
    public const ATTACHABLE_DEAL = Deal::class;

    /**
     * The fully-qualified class name used as the attachable type for Task
     * attachments.
     */
    public const ATTACHABLE_TASK = Task::class;

    /**
     * The fully-qualified class name used as the attachable type for User
     * attachments.
     */
    public const ATTACHABLE_USER = User::class;

    /**
     * All valid attachable types that an attachment can be associated with.
     */
    public const ATTACHABLE_TYPES = [
        self::ATTACHABLE_COMPANY,
        self::ATTACHABLE_DEAL,
        self::ATTACHABLE_TASK,
        self::ATTACHABLE_USER,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int,string>
     */
    protected $appends = ['download_url'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
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
     * Get the polymorphic attachable model this attachment belongs to.
     *
     * The attachment may be a Company, Deal, Task, or User as defined
     * in ATTACHABLE_TYPES.
     *
     * @return MorphTo<Model,Attachment>
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded the attachment.
     *
     * Note: This is separate from the creator relationship, as the uploader may
     * be different from the user that created the attachment record in the
     * database.
     *
     * @return BelongsTo<User,Attachment>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user that created the attachment.
     *
     * This is separate from the uploader relationship, as the creator may
     * be different from the user that uploaded the file, especially in cases
     * where attachments are created programmatically or imported from
     * another system.
     *
     * @return BelongsTo<User,Attachment>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the attachment.
     *
     * This is separate from the uploader relationship, as the updater may
     * be different from the user that uploaded the file, especially in cases
     * where attachments are updated programmatically or by a different user
     * than the original uploader.
     *
     * @return BelongsTo<User,Attachment>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the attachment.
     *
     * This is separate from the uploader relationship, as the deleter may
     * be different from the user that uploaded the file, especially in cases
     * where attachments are deleted programmatically or by a different user
     * than the original uploader.
     *
     * @return BelongsTo<User,Attachment>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the attachment.
     *
     * This is separate from the uploader relationship, as the restorer may
     * be different from the user that uploaded the file, especially in cases
     * where  attachments are restored programmatically or by a different
     * user than the original uploader.
     *
     * @return BelongsTo<User,Attachment>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all activities associated with the attachment.
     *
     * This relationship allows you to retrieve all activity records that
     * are linked to this attachment as the subject. Activities may include
     * events such as creation, updates, deletions, or any other actions
     * that have been recorded in the system related to this attachment.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the attachment.
     *
     * This relationship allows you to retrieve all task records
     * that are linked to this attachment as the taskable.
     * Tasks may include any to-do items, follow-ups,  or action
     * items that have been created in the system related to
     * this attachment.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the attachment.
     *
     * This relationship allows you to retrieve all note records
     * that are linked to  this attachment as the notable.
     * Notes may include any comments, annotations, or additional
     * information that have been created in the system related to
     * this attachment.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the size of the attachment in a human-readable format.
     *
     * This accessor converts the raw size in bytes into a more
     * user-friendly format using appropriate units (B, KB, MB,
     * GB, TB) based on the size of the file.
     * It calculates the factor to determine the correct unit
     * and formats the size to two decimal places for display.
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
     * This accessor simply returns the MIME type stored in the 'mime' attribute
     * of the model.
     * The MIME type is a standard way to indicate the nature and format of a
     * file, such as 'image/jpeg' for JPEG images or 'application/pdf'
     * for PDF documents.
     *
     * @return string
     */
    public function getMimeTypeAttribute(): string
    {
        return $this->mime;
    }

    /**
     * Get the authenticated download URL for the attachment.
     *
     * For locally stored files, returns a signed application route that
     * streams the file through the Laravel download endpoint. For files
     * on external disks (e.g. S3), delegates to the filesystem adapter
     * to generate a direct URL.
     *
     * @return string
     */
    public function getDownloadUrlAttribute(): string
    {
        return app(AttachmentFileService::class)->getUrl($this);
    }

    /**
     * Get the filename without the extension.
     *
     * This accessor uses the PHP built-in function `pathinfo` to extract
     * the filename without the extension from the 'filename' attribute.
     * It returns just the name part of the filename, excluding the
     * extension, which can be useful for display or when you want to
     * manipulate the filename without worrying about the extension.
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
     * This accessor uses the PHP built-in function `pathinfo` to extract
     * the file extension *from the 'filename' attribute. It returns just
     * the extension part of the filename, which can be useful for
     * determining the file type or for display purposes.
     *
     * @return string
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    /**
     * Scope a query to exclude test records.
     *
     * This scope filters the query to include only users where the
     * 'is_test' attribute is false, effectively excluding any users
     * that are marked as test records. This is useful for ensuring
     * that queries return only real user records in the system.
     *
     * @param  Builder<Attachnent> $query The query builder instance.
     *
     * @return Builder<Attachnent>
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * Scope a query to a specific attachable type.
     *
     * This scope filters the query to include only attachments
     * that are associated with a specific attachable type,
     * such as Company, Deal, Task, or User. The method checks
     * if the provided type is valid (i.e., it exists in the
     * ATTACHABLE_TYPES constant) and then applies a where
     * clause to filter by the 'attachable_type' column.
     *
     * @param  Builder<Attachment> $query The query builder instance.
     * @param  string  $type  The fully-qualified class name of
     * the attachable type to filter by.
     *
     * @return Builder<Attachment> The modified query builder instance.
     */
    public function scopeAttachableType(
        Builder $query,
        string $type
    ): Builder {
        if (! in_array($type, self::ATTACHABLE_TYPES)) {
            throw new InvalidArgumentException(
                "Invalid attachable type: {$type}"
            );
        }

        return $query->where('attachable_type', $type);
    }

    /**
     * Scope a query to a specific attachable ID.
     *
     * This scope filters the query to include only attachments that are
     * associated with a specific attachable ID. It applies a where clause
     * to filter by the 'attachable_id' column, allowing you to retrieve
     * attachments that belong to a specific parent model instance (e.g.,
     * all attachments for a company with ID 1).
     *
     * @param  Builder<Attachment> $query The query builder instance.
     * @param  int  $id  The ID of the attachable parent model to filter by.
     *
     * @return Builder<Attachment> The modified query builder instance.
     */
    public function scopeAttachableId(
        Builder $query,
        int $id
    ): Builder {
        return $query->where('attachable_id', $id);
    }
    /**
     * Delete the attachment file from storage when the model is deleted.
     *
     * This method is called automatically by Eloquent when an attachment
     * model is being deleted.
     * It listens for the 'deleting' event and uses the Storage facade
     * to delete the file from the configured disk and path specified
     * in the attachment's attributes. This ensures that when an
     * attachment record is removed from the database, the corresponding
     * file is also removed from storage, preventing orphaned files
     * and saving storage space.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function (Attachment $attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        });
    }
}
