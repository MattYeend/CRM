<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents an invoice issued to a company.
 *
 * Tracks the invoice number, issue and due dates, financial totals, currency,
 * and lifecycle status. An invoice has many line items and exposes computed
 * accessors for common status checks such as overdue, paid, and draft.
 */
class Invoice extends Model
{
    /**
     * @use HasFactory<\Database\Factories\InvoiceFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * Represents an invoice that has not yet been sent.
     */
    public const STATUS_DRAFT = 'draft';

    /**
     * Represents an invoice that has been sent to the company.
     */
    public const STATUS_SENT = 'sent';

    /**
     * Represents an invoice that has been fully paid.
     */
    public const STATUS_PAID = 'paid';

    /**
     * Represents an invoice whose due date has passed without payment.
     */
    public const STATUS_OVERDUE = 'overdue';

    /**
     * Represents an invoice that has been cancelled.
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'number',
        'company_id',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'tax',
        'total',
        'currency',
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
        'issue_date' => 'date',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the company that owns the invoice.
     *
     * @return BelongsTo<Company,Invoice>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the items for the invoice.
     *
     * @return HasMany<InvoiceItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the user who created the invoice.
     *
     * @return BelongsTo<User,Invoice>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the invoice.
     *
     * @return BelongsTo<User,Invoice>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the invoice.
     *
     * @return BelongsTo<User,Invoice>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the invoice.
     *
     * @return BelongsTo<User,Invoice>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the invoice.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the invoice.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the invoice.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the invoice.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Determine whether the invoice is overdue.
     *
     * Returns true when the due date has passed and the invoice has not
     * been paid.
     *
     * @return bool
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date < now() && $this->status !== self::STATUS_PAID;
    }

    /**
     * Determine whether the invoice is paid.
     *
     * @return bool
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Determine whether the invoice is in draft status.
     *
     * @return bool
     */
    public function getIsDraftAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Get the invoice number, applying the test prefix when marked as a test.
     *
     * @param  string|null  $value  The raw invoice number from the database.
     *
     * @return string
     */
    public function getNumberAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
