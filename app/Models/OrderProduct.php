<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Pivot model representing the many-to-many relationship between orders
 * and products.
 *
 * Stores the quantity, unit price, and line total for each product on an
 * order, along with the standard audit and soft-delete tracking columns.
 *
 * Relationships defined in this model include:
 * - order(): The order that this product entry belongs to.
 * - product(): The product that this order entry belongs to.
 * - creator(): The user that created the record.
 * - updater(): The user that updated the record.
 * - deleter(): The user that deleted the record (if soft-deleted).
 * - restorer(): The user that restored the record (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $orderProduct = OrderProduct::find(1);
 * $order = $orderProduct->order; // Get the associated order
 * $product = $orderProduct->product; // Get the associated product
 * $creator = $orderProduct->creator; // Get the user that created the record
 * $updater = $orderProduct->updater; // Get the user that updated the record
 * $deleter = $orderProduct->deleter; // Get the user that deleted the record (if applicable)
 * $restorer = $orderProduct->restorer; // Get the user that restored the record (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getFormattedPriceAttribute(): Returns the unit price formatted to two decimal places as a string.
 * - getFormattedTotalAttribute(): Returns the line item total formatted to two decimal places as a string.
 * - getCalculatedTotalAttribute(): Returns the calculated line item total based on quantity and unit price.
 * Example usage of accessors:
 * ```php
 * $orderProduct = OrderProduct::find(1);
 * $price = $orderProduct->formatted_price; // e.g. "19.99"
 * $total = $orderProduct->formatted_total; // e.g. "59.97"
 * $calculatedTotal = $orderProduct->calculated_total; // e.g. 59.97 (quantity * price)
 * ```
 *
 * Query scopes include:
 * - scopeForOrder($query, $orderId): Filter the query to only include line items for a given order ID.
 * - scopeForProduct($query, $productId): Filter the query to only include line items for a given product ID.
 * - scopeReal($query): Filter the query to only include non-test order products.
 * Example usage of query scopes:
 * ```php
 * $orderProductsForOrder = OrderProduct::forOrder($orderId)->get(); // Get all line items for a specific order
 * $orderProductsForProduct = OrderProduct::forProduct($productId)->get(); // Get all line items for a specific product
 * $realOrderProducts = OrderProduct::real()->get(); // Get all non-test order products
 * ```
 */
class OrderProduct extends Pivot
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
    protected $table = 'order_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'order_id',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the order that this product entry belongs to.
     *
     * @return BelongsTo<Order,OrderProduct>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that this order entry belongs to.
     *
     * @return BelongsTo<Product,OrderProduct>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that created the record.
     *
     * @return BelongsTo<User,OrderProduct>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the record.
     *
     * @return BelongsTo<User,OrderProduct>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the record.
     *
     * @return BelongsTo<User,OrderProduct>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the record.
     *
     * @return BelongsTo<User,OrderProduct>
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
     * Scope a query to only include line items for a given order.
     *
     * Filters by the 'order_id' column. Useful for loading all products
     * on a specific order without going through the Order model's
     * relationship, for example when building a flat line-item report.
     *
     * @param  Builder<OrderProduct> $query The query builder instance.
     * @param  int $order_id  The ID of the order to filter by.
     *
     * @return Builder<OrderProduct> The modified query builder instance.
     */
    public function scopeForOrder(
        Builder $query,
        int $orderId
    ): Builder {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope a query to only include line items for a given product.
     *
     * Filters by the 'product_id' column. Useful for identifying all
     * order that include a particular product, for example when assessing
     * the commercial reach of a product or its pricing history.
     *
     * @param  Builder<OrderProduct> $query The query builder instance.
     * @param  int $productId The ID of the product to filter by.
     *
     * @return Builder<OrderProduct> The modified query builder instance.
     */
    public function scopeForProduct(
        Builder $query,
        int $productId
    ): Builder {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include non-test order products.
     *
     * @param Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
