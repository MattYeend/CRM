<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a sellable product within the system.
 *
 * Products track pricing, stock levels, and lifecycle status
 * (active, discontinued, pending, or out of stock). They support
 * inventory management features such as reorder points, lead times,
 * and stock thresholds.
 *
 * Products are associated with commercial entities such as deals,
 * quotes, and orders, and support related activities like tasks,
 * notes, and attachments. Products may also be marked as test records,
 * in which case certain attributes (e.g. name) are automatically prefixed.
 */
class Product extends Model
{
    /**
     * @use HasFactory<\Database\Factories\ProductFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * Active product status.
     */
    public const ACTIVE_PRODUCT_STATUS = 'active';

    /**
     * Discontinued product status.
     */
    public const DISCONTINUED_PRODUCT_STATUS = 'discontinued';

    /**
     * Pending product status.
     */
    public const PENDING_PRODUCT_STATUS = 'pending';

    /**
     * Out-of-stock product status.
     */
    public const OUT_OF_STOCK_PRODUCT_STATUS = 'out_of_stock';

    /**
     * All valid product statuses.
     *
     * Suitable for validation and filtering logic.
     */
    public const PRODUCT_STATUSES = [
        self::ACTIVE_PRODUCT_STATUS,
        self::DISCONTINUED_PRODUCT_STATUS,
        self::PENDING_PRODUCT_STATUS,
        self::OUT_OF_STOCK_PRODUCT_STATUS,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'currency',
        'status',
        'quantity',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'reorder_quantity',
        'lead_time_days',
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
        'price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the invoice items associated with the product.
     *
     * @return HasMany<InvoiceItem>
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the user that created the product.
     *
     * @return BelongsTo<User,Product>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the product.
     *
     * @return BelongsTo<User,Product>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the product.
     *
     * @return BelongsTo<User,Product>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the product.
     *
     * @return BelongsTo<User,Product>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the product.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the product.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the product.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the product.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the deals associated with the product.
     *
     * Includes pivot data such as quantity and price.
     *
     * @return BelongsToMany<Deal>
     */
    public function deals(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class, 'deal_products')
            ->withPivot('quantity', 'price', 'deleted_at')
            ->withTimestamps()
            ->using(DealProduct::class);
    }

    /**
     * Get the quotes associated with the product.
     *
     * Includes pivot data such as quantity and price.
     *
     * @return BelongsToMany<Quote>
     */
    public function quotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'quote_products')
            ->withPivot('quantity', 'price', 'deleted_at')
            ->withTimestamps()
            ->using(QuoteProduct::class);
    }

    /**
     * Get the orders associated with the product.
     *
     * Includes pivot data such as quantity, price, and metadata.
     *
     * @return BelongsToMany<Order>
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_products')
            ->withPivot('quantity', 'price', 'meta', 'deleted_at')
            ->withTimestamps()
            ->using(OrderProduct::class);
    }

    /**
     * Scope a query to products that are low on stock.
     *
     * Compares quantity against the reorder point.
     *
     * @param  Builder<Product> $query The query builder instance.
     *
     * @return Builder<Product> The modified query builder instance.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'reorder_point');
    }

    /**
     * Scope a query to products that are out of stock.
     *
     * @param  Builder<Product> $query The query builder instance.
     *
     * @return Builder<Product> The modified query builder instance.
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('quantity', 0);
    }

    /**
     * Determine whether the product is low on stock.
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->reorder_point && $this->quantity <= $this->reorder_point;
    }

    /**
     * Determine whether the product is out of stock.
     *
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    /**
     * Get the formatted product name.
     *
     * Applies a test prefix when the product is marked as a test record.
     *
     * @param  string|null $value The raw product name from the database.
     *
     * @return string The formatted product name.
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
