<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
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
 */
class Order extends Model
{
    /**
     * @use HasFactory<\Database\Factories\OrderFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

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
        'user_id',
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
     * Mark the order as paid and record the payment timestamp.
     *
     * @return bool True if the model was successfully updated.
     */
    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Get the products associated with the order.
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->using(OrderProduct::class)
            ->withPivot(['quantity', 'price', 'total'])
            ->withTimestamps();
    }
}
