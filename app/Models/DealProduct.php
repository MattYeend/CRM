<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Pivot model representing the many-to-many relationship between deals
 * and products.
 *
 * Stores the quantity, unit price, and line total for each product on a
 * deal, along with the standard audit and soft-delete tracking columns.
 *
 * Relationships defined in this model include:
 * - deal(): Belongs-to relationship to the Deal this line item belongs to.
 * - product(): Belongs-to relationship to the Product referenced by this
 *      line item.
 * - creator(): Belongs-to relationship to the User who created the record.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      record.
 * - deleter(): Belongs-to relationship to the User who deleted the record
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the record
 *      (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $lineItem = DealProduct::find(1);
 * $deal = $lineItem->deal; // Get the parent deal
 * $product = $lineItem->product; // Get the associated product
 * $creator = $lineItem->creator; // Get the user that created the record
 * $updater = $lineItem->updater; // Get the user that last updated the record
 * $deleter = $lineItem->deleter; // Get the user that deleted the record
 *  (if applicable)
 * $restorer = $lineItem->restorer; // Get the user that restored the record
 *  (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getFormattedPriceAttribute(): Returns the unit price formatted to two
 *      decimal places as a string.
 * - getFormattedTotalAttribute(): Returns the line item total formatted to
 *      two decimal places as a string.
 * - getCalculatedTotalAttribute(): Returns the line total derived from
 *      quantity multiplied by unit price.
 * Example usage of accessors:
 * ```php
 * $lineItem = DealProduct::find(1);
 * $price = $lineItem->formatted_price; // e.g. "99.00"
 * $total = $lineItem->formatted_total; // e.g. "297.00"
 * $calculatedTotal = $lineItem->calculated_total; // float: 297.0
 * ```
 *
 * Query scopes include:
 * - scopeForDeal($query, $dealId): Filter the query to only include line
 *      items for a given deal.
 * - scopeForProduct($query, $productId): Filter the query to only include
 *      line items for a given product.
 * - scopeReal($query): Filter the query to only include non-test records.
 * Example usage of query scopes:
 * ```php
 * $items = DealProduct::forDeal($dealId)->get(); // Line items on a deal
 * $entries = DealProduct::forProduct($productId)->get(); // All entries
 *  for a product
 * $real = DealProduct::real()->get(); // Exclude test records
 * ```
 */
class DealProduct extends Pivot
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
    protected $table = 'deal_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'deal_id',
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

    /**
     * Get the deal this line item belongs to.
     *
     * @return BelongsTo<Deal,DealProduct>
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Get the product associated with this line item.
     *
     * @return BelongsTo<Product,DealProduct>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that created the line item.
     *
     * @return BelongsTo<User,DealProduct>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the line item.
     *
     * @return BelongsTo<User,DealProduct>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the line item.
     *
     * @return BelongsTo<User,DealProduct>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the line item.
     *
     * @return BelongsTo<User,DealProduct>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the unit price formatted to two decimal places.
     *
     * Returns the price as a string without currency symbols. Pair with
     * the parent deal's currency field for a fully formatted display value.
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
     * Returns the stored total as a string without currency symbols. Pair
     * with the parent deal's currency field for a fully formatted display
     * value suitable for deal summaries or proposal exports.
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
     * Multiplies the quantity by the unit price to derive the expected line
     * total. Useful for verifying or recalculating the stored total when
     * either value has been updated independently.
     *
     * @return float
     */
    public function getCalculatedTotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->price;
    }

    /**
     * Scope a query to only include line items for a given deal.
     *
     * Filters by the 'deal_id' column. Useful for loading all products on
     * a specific deal without going through the Deal model's relationship,
     * for example when building a flat product summary report.
     *
     * @param  Builder<DealProduct> $query The query builder instance.
     * @param  int $dealId The ID of the deal to filter by.
     *
     * @return Builder<DealProduct> The modified query builder instance.
     */
    public function scopeForDeal(Builder $query, int $dealId): Builder
    {
        return $query->where('deal_id', $dealId);
    }

    /**
     * Scope a query to only include line items for a given product.
     *
     * Filters by the 'product_id' column. Useful for identifying all deals
     * that include a particular product, for example when assessing a
     * product's commercial reach or pricing history.
     *
     * @param  Builder<DealProduct> $query The query builder instance.
     * @param  int $productId The ID of the product to filter by.
     *
     * @return Builder<DealProduct> The modified query builder instance.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include non-test line items.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring
     * that queries return only real deal product records. Important for
     * accurate deal valuation and pipeline reporting.
     *
     * @param  Builder<DealProduct> $query The query builder instance.
     *
     * @return Builder<DealProduct> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
