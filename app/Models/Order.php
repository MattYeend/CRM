<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a payment order associated with a deal and a user.
 *
 * Tracks the order amount, currency, payment method, Stripe identifiers,
 * and lifecycle status. Exposes a markAsPaid convenience method and a
 * scopePaid query scope for filtering paid orders. Products are associated
 * via the order_products pivot table.
 *
 * Relationships defined in this model include:
 * - user(): The user that owns the order.
 * - deal(): The deal associated with the order.
 * - products(): The products associated with the order.
 * - creator(): The user that created the order.
 * - updater(): The user that last updated the order.
 * - deleter(): The user that deleted the order (if soft-deleted).
 * - restorer(): The user that restored the order (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $order = Order::find(1);
 * $user = $order->user; // Get the user that owns this order
 * $deal = $order->deal; // Get the deal associated with this order
 * $products = $order->products; // Get the products in this order
 * $creator = $order->creator; // Get the user that created this order
 * $updater = $order->updater; // Get the user that last updated this order
 * $deleter = $order->deleter; // Get the user that deleted this order
 *  (if applicable)
 * $restorer = $order->restorer; // Get the user that restored this order
 *  (if applicable)
 * ```
 *
 * Helper methods include:
 * - getMarkAsPaid(): Mark the order as paid and record the payment
 *      timestamp.
 * - getMarkAsFailed(): Mark the order as failed and record the payment
 *      timestamp.
 * - getMarkAsPending(): Mark the order as pending and clear the payment
 *      timestamp.
 * Example usage of helper methods:
 * ```php
 * $order = Order::find(1);
 * $order->markAsPaid(); // Mark the order as paid
 * $order->markAsFailed(); // Mark the order as failed
 * $order->markAsPending(); // Mark the order as pending
 * ```
 *
 * Query scopes include:
 * - scopePending($query): Filter the query to only include pending orders.
 * - scopeFailed($query): Filter the query to only include failed orders.
 * - scopeNotPaid($query): Filter the query to only include orders that
 *      are not paid (i.e. pending or failed).
 * - scopePaid($query): Filter the query to only include paid orders.
 * - scopeSearch($query, $term): Filter the query to include orders
 *      matching a search term in the ID, amount, or currency.
 * - scopeReal($query): Filter the query to only include non-test orders.
 * Example usage of query scopes:
 * ```php
 * $pendingOrders = Order::pending()->get(); // Get all pending orders
 * $failedOrders = Order::failed()->get(); // Get all failed orders
 * $notPaidOrders = Order::notPaid()->get(); // Get all orders that are not
 *  paid
 * $paidOrders = Order::paid()->get(); // Get all paid orders
 * $searchResults = Order::search('USD')->get(); // Get orders matching the
 *  search term "USD"
 * $realOrders = Order::real()->get(); // Get all non-test orders
 * ```
 */
class Order extends Model
{
    /**
     * @use HasFactory<\Database\Factories\OrderFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * Represents a pending order awaiting payment.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Represents an order that has been successfully paid.
     */
    public const STATUS_PAID = 'paid';

    /**
     * Represents an order whose payment attempt failed.
     */
    public const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'deal_id',
        'assigned_to',
        'amount',
        'currency',
        'status',
        'payment_method',
        'paid_at',
        'payment_intent_id',
        'charge_id',
        'stripe_payment_intent',
        'stripe_invoice_id',
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
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the user who owns the order.
     *
     * @return BelongsTo<User,Order>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the deal associated with the order.
     *
     * @return BelongsTo<Deal,Order>
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Get the products associated with the order.
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'order_products',
            'order_id',
            'product_id'
        )
            ->using(OrderProduct::class)
            ->withPivot(['quantity', 'price', 'total', 'meta', 'deleted_at'])
            ->withTimestamps()
            ->whereNull('order_products.deleted_at');
    }

    /**
     * Get the user that created the order.
     *
     * @return BelongsTo<User,Order>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the order.
     *
     * @return BelongsTo<User,Order>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the order.
     *
     * @return BelongsTo<User,Order>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the order.
     *
     * @return BelongsTo<User,Order>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Mark the order as paid and record the payment timestamp.
     *
     * @return bool True if the model was successfully updated.
     */
    public function getMarkAsPaid(): bool
    {
        return $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark the order as failed and record the payment timestamp.
     *
     * @return bool True if the model was successfully updated.
     */
    public function getMarkAsFailed(): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark the order as pending and clear the payment timestamp.
     *
     * @return bool True if the model was successfully updated.
     */
    public function getMarkAsPending(): bool
    {
        return $this->update([
            'status' => self::STATUS_PENDING,
            'paid_at' => null,
        ]);
    }

    /**
     * Scope a query to only include pending orders.
     *
     * @param  Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include failed orders.
     *
     * @param  Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope a query to only include orders that are not paid
     * (i.e. pending or failed).
     *
     * @param  Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeNotPaid(Builder $query): Builder
    {
        return $query->whereIn(
            'status',
            [self::STATUS_PENDING, self::STATUS_FAILED]
        );
    }

    /**
     * Scope a query to only include paid orders.
     *
     * @param  Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope a search query to include orders matching a search term in
     * the ID, amount, or currency.
     *
     * @param  Builder $query The query builder instance.
     * @param  string|null $term The search term to filter by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $like = "%{$term}%";

        return $query->where(function (Builder $q) use ($like) {
            $q->where('id', 'like', "%{$like}%")
                ->orWhere('amount', 'like', "%{$like}%")
                ->orWhere('currency', 'like', "%{$like}%");
        });
    }

    /**
     * Scope a query to only include non-test orders.
     *
     * @param  Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
