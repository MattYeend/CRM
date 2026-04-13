<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
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
 *
 * Relationships defined in this model include:
 * - company(): Belongs-to relationship to the Company this invoice was
 *      issued to.
 * - items(): One-to-many relationship to InvoiceItem records that make
 *      up the invoice's line items.
 * - attachments(): Polymorphic one-to-many relationship to Attachment
 *      records associated with the invoice.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the invoice.
 * - tasks(): Polymorphic one-to-many relationship to Task records
 *      associated with the invoice.
 * - notes(): Polymorphic one-to-many relationship to Note records
 *      associated with the invoice.
 * - creator(): Belongs-to relationship to the User who created the invoice.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      invoice.
 * - deleter(): Belongs-to relationship to the User who deleted the invoice
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the invoice
 *      (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $invoice = Invoice::find(1);
 * $company = $invoice->company; // Get the company this invoice was issued to
 * $items = $invoice->items; // Get all line items on the invoice
 * $creator = $invoice->creator; // Get the user that created the invoice
 * $updater = $invoice->updater; // Get the user that last updated the invoice
 * $deleter = $invoice->deleter; // Get the user that deleted the invoice
 *  (if applicable)
 * $restorer = $invoice->restorer; // Get the user that restored the invoice
 *  (if applicable)
 * $attachments = $invoice->attachments; // Get all attachments for the invoice
 * $activities = $invoice->activities; // Get all activities for the invoice
 * $tasks = $invoice->tasks; // Get all tasks for the invoice
 * $notes = $invoice->notes; // Get all notes for the invoice
 * ```
 *
 * Other methods include:
 * - recalculateTotals(): Recalculates and persists the invoice subtotal and
 *      total by summing the line totals of all non-deleted items. Called
 *      automatically by InvoiceItemObserver whenever a line item is saved,
 *      deleted, or restored.
 * Example usage of other methods:
 * ```php
 * $invoice->recalculateTotals(); // Sync totals after bulk item changes
 * ```
 *
 * Accessor methods include:
 * - getNumberAttribute(): Returns the invoice number, applying a test prefix
 *      if the invoice is marked as a test record.
 * - getIsOverdueAttribute(): Returns a boolean indicating whether the invoice
 *      is past its due date and has not been paid.
 * - getIsPaidAttribute(): Returns a boolean indicating whether the invoice
 *      has been fully paid.
 * - getIsDraftAttribute(): Returns a boolean indicating whether the invoice
 *      is in draft status.
 * - getIsSentAttribute(): Returns a boolean indicating whether the invoice
 *      has been sent to the company.
 * - getIsCancelledAttribute(): Returns a boolean indicating whether the
 *      invoice has been cancelled.
 * - getFormattedSubtotalAttribute(): Returns the subtotal formatted to two
 *      decimal places as a string.
 * - getFormattedTaxAttribute(): Returns the tax amount formatted to two
 *      decimal places as a string.
 * - getFormattedTotalAttribute(): Returns the total formatted to two decimal
 *      places as a string.
 * Example usage of accessors:
 * ```php
 * $invoice = Invoice::find(1);
 * $number = $invoice->number; // Get the number with test prefix
 * $isOverdue = $invoice->is_overdue; // Check if past due date and unpaid
 * $isPaid = $invoice->is_paid; // Check if fully paid
 * $isDraft = $invoice->is_draft; // Check if still a draft
 * $total = $invoice->formatted_total; // e.g. "1440.00"
 * ```
 *
 * Query scopes include:
 * - scopeDraft($query): Filter the query to only include draft invoices.
 * - scopeSent($query): Filter the query to only include sent invoices.
 * - scopePaid($query): Filter the query to only include paid invoices.
 * - scopeOverdue($query): Filter the query to only include overdue invoices.
 * - scopeCancelled($query): Filter the query to only include cancelled
 *      invoices.
 * - scopeWithStatus($query, $status): Filter the query to only include
 *      invoices with a given status or list of statuses.
 * - scopeOutstanding($query): Filter the query to only include invoices
 *      that have been sent but not yet paid.
 * - scopeForCompany($query, $companyId): Filter the query to only include
 *      invoices belonging to a given company.
 * - scopeInCurrency($query, $currency): Filter the query to only include
 *      invoices in a given currency or list of currencies.
 * - scopeDueBefore($query, $date): Filter the query to only include invoices
 *      due before a given date.
 * - scopeReal($query): Filter the query to only include non-test invoices.
 * Example usage of query scopes:
 * ```php
 * $overdue = Invoice::overdue()->get(); // Get overdue invoices
 * $outstanding = Invoice::outstanding()->get(); // Sent but unpaid
 * $gbp = Invoice::inCurrency('GBP')->get(); // GBP invoices only
 * $company = Invoice::forCompany($companyId)->get(); // Invoices for a company
 * $real = Invoice::real()->get(); // Exclude test records
 * ```
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
        'subtotal' => 'float',
        'tax' => 'float',
        'total' => 'float',
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
     * Recalculate and persist the invoice totals from its current line items.
     *
     * Sums the line totals of all non-deleted items to derive the subtotal,
     * then adds the existing tax amount to produce the new total. Call this
     * whenever items are created, updated, or deleted.
     *
     * @return void
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');

        $this->subtotal = $subtotal;
        $this->total    = $subtotal + $this->tax;

        $this->saveQuietly();
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

    /**
     * Determine whether the invoice is overdue.
     *
     * Returns true when the due date has passed and the invoice has not
     * been paid or cancelled. Useful for dashboard indicators and automated
     * chasing workflows.
     *
     * @return bool
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! in_array($this->status, [
                self::STATUS_PAID,
                self::STATUS_CANCELLED,
            ], true);
    }

    /**
     * Determine whether the invoice has been fully paid.
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
     * Draft invoices have not yet been sent and may still be modified
     * before dispatch.
     *
     * @return bool
     */
    public function getIsDraftAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Determine whether the invoice has been sent to the company.
     *
     * @return bool
     */
    public function getIsSentAttribute(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Determine whether the invoice has been cancelled.
     *
     * @return bool
     */
    public function getIsCancelledAttribute(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get the subtotal formatted to two decimal places.
     *
     * Returns the subtotal as a string without currency symbols. Pair with
     * the invoice's currency field for a fully formatted display value.
     *
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format((float) $this->subtotal, 2, '.', '');
    }

    /**
     * Get the tax amount formatted to two decimal places.
     *
     * Returns the tax as a string without currency symbols. Pair with
     * the invoice's currency field for a fully formatted display value.
     *
     * @return string
     */
    public function getFormattedTaxAttribute(): string
    {
        return number_format((float) $this->tax, 2, '.', '');
    }

    /**
     * Get the total formatted to two decimal places.
     *
     * Returns the total as a string without currency symbols. Pair with
     * the invoice's currency field for a fully formatted display value
     * suitable for PDF exports and UI summaries.
     *
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 2, '.', '');
    }

    /**
     * Scope a query to only include draft invoices.
     *
     * @param  Builder<Invoice> $query The query builder instance.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope a query to only include sent invoices.
     *
     * @param  Builder<Invoice> $query The query builder instance.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope a query to only include paid invoices.
     *
     * @param  Builder<Invoice> $query The query builder instance.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope a query to only include overdue invoices.
     *
     * An invoice is overdue when its due date is in the past and its status
     * is neither paid nor cancelled.
     *
     * @param  Builder<Invoice> $query The query builder instance.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', [
                self::STATUS_PAID,
                self::STATUS_CANCELLED,
            ]);
    }

    /**
     * Scope a query to only include cancelled invoices.
     *
     * @param  Builder<Invoice> $query  The query builder instance.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope a query to only include invoices with a given status or
     * list of statuses.
     *
     * Accepts either a single status string or an array of status values.
     * Values should match one of the STATUS_* constants defined on this model.
     *
     * @param  Builder<Invoice> $query The query builder instance.
     * @param  string|array<int,string> $status The status or statuses to
     * filter by.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeWithStatus(
        Builder $query,
        string|array $status
    ): Builder {
        return is_array($status)
            ? $query->whereIn('status', $status)
            : $query->where('status', $status);
    }

    /**
     * Scope a query to only include outstanding invoices.
     *
     * An invoice is outstanding when it has been sent but not yet paid.
     * Useful for collections workflows and accounts receivable reporting.
     *
     * @param  Builder<Invoice> $query The query builder instance.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            self::STATUS_PAID,
            self::STATUS_CANCELLED,
            self::STATUS_DRAFT,
        ]);
    }

    /**
     * Scope a query to only include invoices belonging to a given company.
     *
     * Filters by the 'company_id' column. Useful for loading all invoices
     * for a specific company without going through the Company model's
     * relationship.
     *
     * @param  Builder<Invoice> $query The query builder instance.
     * @param  int $companyId The ID of the company to filter by.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include invoices in a given currency or list
     * of currencies.
     *
     * Accepts either a single currency code as a string or an array of
     * currency codes. Applies a where or whereIn clause accordingly.
     *
     * @param  Builder<Invoice> $query The query builder instance.
     * @param  string|array<int,string> $currency The currency code or
     * codes to filter by.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeInCurrency(
        Builder $query,
        string|array $currency
    ): Builder {
        return is_array($currency)
            ? $query->whereIn('currency', $currency)
            : $query->where('currency', $currency);
    }

    /**
     * Scope a query to only include invoices due before a given date.
     *
     * Useful for identifying upcoming or overdue payment obligations within
     * a specific timeframe, for example in aged debt reports.
     *
     * @param  Builder<Invoice> $query  The query builder instance.
     * @param  \DateTimeInterface|string $date   The cutoff date.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeDueBefore(Builder $query, $date): Builder
    {
        return $query->where('due_date', '<', $date);
    }

    /**
     * Scope a query to only include non-test invoices.
     *
     * Filters out any invoice records where the 'is_test' flag is true,
     * ensuring that queries return only real invoice records. Important for
     * accurate financial reporting and accounts receivable workflows.
     *
     * @param  Builder<Invoice> $query The query builder instance.
     *
     * @return Builder<Invoice> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
