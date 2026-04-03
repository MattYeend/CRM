<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a single line item on an invoice.
 *
 * Each item is associated with an invoice and optionally a product, and
 * carries a description, quantity, unit price, and a computed line total.
 *
 * Relationships defined in this model include:
 * - invoice(): Belongs-to relationship to the Invoice that owns this item.
 * - product(): Belongs-to relationship to the Product referenced by this
 *      item, which may be null for bespoke or non-catalogue line items.
 * - attachments(): Polymorphic one-to-many relationship to Attachment
 *      records associated with the invoice item.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the invoice item.
 * - tasks(): Polymorphic one-to-many relationship to Task records
 *      associated with the invoice item.
 * - notes(): Polymorphic one-to-many relationship to Note records
 *      associated with the invoice item.
 * - creator(): Belongs-to relationship to the User who created the record.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      record.
 * - deleter(): Belongs-to relationship to the User who deleted the record
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the record
 *      (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $item = InvoiceItem::find(1);
 * $invoice = $item->invoice; // Get the parent invoice
 * $product = $item->product; // Get the associated product (if any)
 * $notes = $item->notes; // Get all notes for this line item
 * ```
 *
 * Accessor methods include:
 * - getDescriptionAttribute(): Returns the description, applying a test
 *      prefix if the record is marked as a test.
 * - getLineTotalAttribute(): Returns the computed line total as quantity
 *      multiplied by unit price.
 * - getFormattedUnitPriceAttribute(): Returns the unit price formatted to
 *      two decimal places as a string.
 * - getFormattedLineTotalAttribute(): Returns the computed line total
 *      formatted to two decimal places as a string.
 * - getHasProductAttribute(): Returns a boolean indicating whether this
 *      line item is linked to a catalogue product.
 * Example usage of accessors:
 * ```php
 * $item = InvoiceItem::find(1);
 * $description = $item->description; // Get description with test prefix
 * $lineTotal = $item->line_total; // Computed float total
 * $formatted = $item->formatted_line_total; // e.g. "149.99"
 * $hasProduct = $item->has_product; // Check if linked to a product
 * ```
 *
 * Query scopes include:
 * - scopeForInvoice($query, $invoiceId): Filter the query to only include
 *      items belonging to a given invoice.
 * - scopeForProduct($query, $productId): Filter the query to only include
 *      items referencing a given product.
 * - scopeWithProduct($query): Filter the query to only include items that
 *      are linked to a catalogue product.
 * - scopeWithoutProduct($query): Filter the query to only include items
 *      that are not linked to any product.
 * - scopeReal($query): Filter the query to only include non-test records.
 * Example usage of query scopes:
 * ```php
 * $items = InvoiceItem::forInvoice($invoiceId)->get(); // Items on an invoice
 * $catalogue = InvoiceItem::withProduct()->get(); // Catalogue-backed items
 * $bespoke = InvoiceItem::withoutProduct()->get(); // Custom line items
 * $real = InvoiceItem::real()->get(); // Exclude test records
 * ```
 */
class InvoiceItem extends Model
{
    /**
     * @use HasFactory<\Database\Factories\InvoiceItemFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
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
        'unit_price' => 'float',
        'line_total' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the invoice that owns the invoice item.
     *
     * @return BelongsTo<Invoice,InvoiceItem>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the product associated with the invoice item.
     *
     * @return BelongsTo<Product,InvoiceItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that created the invoice item.
     *
     * @return BelongsTo<User,InvoiceItem>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the invoice item.
     *
     * @return BelongsTo<User,InvoiceItem>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the invoice item.
     *
     * @return BelongsTo<User,InvoiceItem>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the invoice item.
     *
     * @return BelongsTo<User,InvoiceItem>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the invoice item.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the invoice item.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the invoice item.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the invoice item.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the invoice item description, applying the test prefix when marked
     * as a test.
     *
     * @param  string|null $value The raw description value from the database.
     *
     * @return string
     */
    public function getDescriptionAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Get the computed line total for the invoice item.
     *
     * Calculated as quantity multiplied by unit price. The stored
     * 'line_total' column is overridden by this accessor to ensure the
     * value always reflects the current quantity and unit price, even
     * if the stored column has not been explicitly updated.
     *
     * @return float
     */
    public function getLineTotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    /**
     * Get the unit price formatted to two decimal places.
     *
     * Returns the price as a string without currency symbols. Pair with
     * the parent invoice's currency field for a fully formatted display value.
     *
     * @return string
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return number_format((float) $this->unit_price, 2, '.', '');
    }

    /**
     * Get the computed line total formatted to two decimal places.
     *
     * Returns the total as a string without currency symbols. Pair with
     * the parent invoice's currency field for a fully formatted display value
     * suitable for PDF exports or UI line item tables.
     *
     * @return string
     */
    public function getFormattedLineTotalAttribute(): string
    {
        return number_format($this->line_total, 2, '.', '');
    }

    /**
     * Determine whether this line item is linked to a catalogue product.
     *
     * Returns true when a 'product_id' is set. Items without a product
     * are treated as bespoke or free-text line items.
     *
     * @return bool
     */
    public function getHasProductAttribute(): bool
    {
        return $this->product_id !== null;
    }

    /**
     * Scope a query to only include items belonging to a given invoice.
     *
     * Filters by the 'invoice_id' column. Useful for loading all line items
     * on a specific invoice without going through the Invoice model's
     * relationship, for example when building a flat invoice export.
     *
     * @param  Builder<InvoiceItem> $query The query builder instance.
     * @param  int $invoiceId The ID of the invoice to filter by.
     *
     * @return Builder<InvoiceItem> The modified query builder instance.
     */
    public function scopeForInvoice(Builder $query, int $invoiceId): Builder
    {
        return $query->where('invoice_id', $invoiceId);
    }

    /**
     * Scope a query to only include items referencing a given product.
     *
     * Filters by the 'product_id' column. Useful for identifying all
     * invoices that include a particular product.
     *
     * @param  Builder<InvoiceItem> $query The query builder instance.
     * @param  int $productId The ID of the product to filter by.
     *
     * @return Builder<InvoiceItem> The modified query builder instance.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include items linked to a catalogue product.
     *
     * Filters to items where 'product_id' is not null. Useful for reporting
     * on product revenue or distinguishing catalogue sales from bespoke work.
     *
     * @param  Builder<InvoiceItem> $query The query builder instance.
     *
     * @return Builder<InvoiceItem> The modified query builder instance.
     */
    public function scopeWithProduct(Builder $query): Builder
    {
        return $query->whereNotNull('product_id');
    }

    /**
     * Scope a query to only include items not linked to any product.
     *
     * Filters to items where 'product_id' is null, representing bespoke or
     * free-text line items added directly to the invoice.
     *
     * @param  Builder<InvoiceItem> $query The query builder instance.
     *
     * @return Builder<InvoiceItem> The modified query builder instance.
     */
    public function scopeWithoutProduct(Builder $query): Builder
    {
        return $query->whereNull('product_id');
    }

    /**
     * Scope a query to only include non-test invoice items.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring
     * that queries return only real line item records. Important for
     * accurate financial reporting and invoice summaries.
     *
     * @param  Builder<InvoiceItem> $query The query builder instance.
     *
     * @return Builder<InvoiceItem> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
