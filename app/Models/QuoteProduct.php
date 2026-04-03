<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a pivot record linking quotes and products.
 *
 * Stores line-item details for a product within a quote, including
 * quantity, unit price, and total value. Supports soft deletion and
 * audit tracking, allowing historical changes to be preserved.
 *
 * Additional metadata may be stored for extensibility, and records
 * may be flagged as test data.
 *
 * Relationships defined in this model include:
 * - quote(): Belongs-to relationship to the Quote this line item
 *      belongs to.
 * - product(): Belongs-to relationship to the Product referenced by
 *      this line item.
 * - creator(): Belongs-to relationship to the User who created the
 *      record.
 * - updater(): Belongs-to relationship to the User who last updated
 *      the record.
 * - deleter(): Belongs-to relationship to the User who deleted the
 *      record (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the
 *      record (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $lineItem = QuoteProduct::find(1);
 * $quote = $lineItem->quote; // Get the parent quote
 * $product = $lineItem->product; // Get the associated product
 * $creator = $lineItem->creator; // Get the user that created the record
 * $updater = $lineItem->updater; // Get the user that last updated the record
 * $deleter = $lineItem->deleter; // Get the user that deleted the record
 * (if applicable)
 * $restorer = $lineItem->restorer; // Get the user that restored the record
 * (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getFormattedPriceAttribute(): Returns the unit price formatted to
 *      two decimal places as a string.
 * - getFormattedTotalAttribute(): Returns the line item total formatted
 *      to two decimal places as a string.
 * - getIsTestAttribute(): Returns a boolean indicating whether this
 *      record is flagged as test data.
 * Example usage of accessors:
 * ```php
 * $lineItem = QuoteProduct::find(1);
 * $price = $lineItem->formatted_price; // e.g. "19.99"
 * $total = $lineItem->formatted_total; // e.g. "59.97"
 * $isTest = $lineItem->is_test; // true or false
 * ```
 *
 * Query scopes include:
 * - scopeForQuote($query, $quoteId): Filter the query to only include
 *      line items for a given quote.
 * - scopeForProduct($query, $productId): Filter the query to only
 *      include line items for a given product.
 * - scopeReal($query): Filter the query to only include non-test
 *      line items.
 * Example usage of query scopes:
 * ```php
 * $items = QuoteProduct::forQuote($quoteId)->get(); // Line items for a quote
 * $entries = QuoteProduct::forProduct($productId)->get(); // All entries
 * for a product
 * $real = QuoteProduct::real()->get(); // Exclude test records
 * ```
 */
class QuoteProduct extends Pivot
{
    /**
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     */
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quote_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'quote_id',
        'product_id',
        'quantity',
        'price',
        'total',
        'is_test',
        'meta',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
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
        'price' => 'float',
        'total' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Get the quote this line item belongs to.
     *
     * @return BelongsTo<Quote,QuoteProduct>
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Get the product associated with this line item.
     *
     * @return BelongsTo<Product,QuoteProduct>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that created the line item.
     *
     * @return BelongsTo<User,QuoteProduct>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the line item.
     *
     * @return BelongsTo<User,QuoteProduct>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the line item.
     *
     * @return BelongsTo<User,QuoteProduct>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the line item.
     *
     * @return BelongsTo<User,QuoteProduct>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the unit price formatted to two decimal places.
     *
     * Returns the price as a string rounded to two decimal places,
     * suitable for display in line item tables or PDF exports. Does
     * not apply currency symbols; pair with the parent quote's currency
     * field for a fully formatted value.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 2, '.', '');
    }

    /**
     * Get the line item total formatted to two decimal places.
     *
     * Returns the total as a string rounded to two decimal places,
     * suitable for display in line item tables or PDF exports. Does
     * not apply currency symbols; pair with the parent quote's currency
     * field for a fully formatted value.
     *
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 2, '.', '');
    }

    /**
     * Get the calculated line item total based on quantity and unit price.
     *
     * Multiplies the quantity by the unit price to derive the expected
     * line total. This is useful for verifying or recalculating the stored
     * total, particularly when either value has been updated independently.
     *
     * @return float
     */
    public function getCalculatedTotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->price;
    }

    /**
     * Scope a query to only include line items for a given quote.
     *
     * Filters by the 'quote_id' column. Useful for loading all products
     * on a specific quote without going through the Quote model's
     * relationship, for example when building a flat line-item report.
     *
     * @param  Builder<QuoteProduct> $query The query builder instance.
     * @param  int $quoteId  The ID of the quote to filter by.
     *
     * @return Builder<QuoteProduct> The modified query builder instance.
     */
    public function scopeForQuote(
        Builder $query,
        int $quoteId
    ): Builder {
        return $query->where('quote_id', $quoteId);
    }

    /**
     * Scope a query to only include line items for a given product.
     *
     * Filters by the 'product_id' column. Useful for identifying all
     * quotes that include a particular product, for example when assessing
     * the commercial reach of a product or its pricing history.
     *
     * @param  Builder<QuoteProduct> $query The query builder instance.
     * @param  int $productId The ID of the product to filter by.
     *
     * @return Builder<QuoteProduct> The modified query builder instance.
     */
    public function scopeForProduct(
        Builder $query,
        int $productId
    ): Builder {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include non-test line items.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring
     * that queries return only real line item records. Important for
     * accurate financial reporting and quote summaries.
     *
     * @param  Builder<QuoteProduct> $query  The query builder instance.
     *
     * @return Builder<QuoteProduct> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
