<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * Constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'number',
        'company_id',
        'contact_id',
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
     * @var array<string, string>
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
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the contact that owns the invoice.
     *
     * @return BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the items for the invoice.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the user who created the invoice.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the invoice.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the invoice.
     *
     * @return BelongsTo
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the invoice.
     *
     * @return BelongsTo
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all of the invoice attachments.
     *
     * @return MorphMany
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all of the invoice activities.
     *
     * @return MorphMany
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all of the invoice tasks.
     *
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all of the invoice notes.
     *
     * @return MorphMany
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * See if the invoice is overdue.
     *
     * @return bool
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date < now() && $this->status !== self::STATUS_PAID;
    }

    /**
     * See if the invoice is paid.
     *
     * @return bool
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * See if the invoice is draft.
     *
     * @return bool
     */
    public function getIsDraftAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Get the invoice number.
     *
     * Applies the test prefix when the invoice is marked as a test.
     *
     * @param  string|null  $value  The raw invoice number from the database.
     * @return string
     */
    public function getNumberAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
