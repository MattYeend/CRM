<?php

namespace App\Models;

use App\Traits\BillOfMaterials\HasBillOfMaterials;
use App\Traits\Products\HasStockLevels;
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
 *
 * Bill of Materials Support:
 * Products serve as the manufacturable (parent) in a polymorphic BOM
 * relationship. All products can have bill of materials entries, as the
 * hasBom() method always returns true. This allows products to be assembled
 * from parts, supporting cost calculation and manufacturing workflows.
 *
 * Relationships defined in this model include:
 * - invoiceItems(): One-to-many relationship to InvoiceItem records
 *      referencing this product.
 * - deals(): Many-to-many relationship to Deal records via the
 *      deal_products pivot, including quantity and price.
 * - quotes(): Many-to-many relationship to Quote records via the
 *      quote_products pivot, including quantity and price.
 * - orders(): Many-to-many relationship to Order records via the
 *      order_products pivot, including quantity, price, and metadata.
 * - stockMovements(): All stock movement records for this product.
 * - attachments(): Polymorphic one-to-many relationship to Attachment
 *      records associated with the product.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the product.
 * - tasks(): Polymorphic one-to-many relationship to Task records
 *      associated with the product.
 * - notes(): Polymorphic one-to-many relationship to Note records
 *      associated with the product.
 * - billOfMaterials(): Polymorphic relationship to BOM entries where
 *      this product is the manufacturable (parent assembly).
 * - creator(): Belongs-to relationship to the User who created the product.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      product.
 * - deleter(): Belongs-to relationship to the User who deleted the product
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the product
 *      (if soft-deleted).
 *
 * Example usage of relationships:
 * ```php
 * $product = Product::find(1);
 * $deals = $product->deals; // Get all deals that include this product
 * $quotes = $product->quotes; // Get all quotes that include this product
 * $invoiceItems = $product->invoiceItems; // Get all invoice line items
 * $stockMovements = $product->stockMovements; // Get all stock movements
 * $billOfMaterials = $product->billOfMaterials; // Get all BOM entries
 * $creator = $product->creator; // Get the user that created the product
 * ```
 *
 * Accessor methods include:
 * - getIsActiveAttribute(): Returns a boolean indicating whether the product
 *      has an active status.
 * - getIsDiscontinuedAttribute(): Returns a boolean indicating whether the
 *      product has been discontinued.
 * - getIsLowStockAttribute(): Returns a boolean indicating whether the
 *      product's quantity is at or below the reorder point.
 * - getIsOutOfStockAttribute(): Returns a boolean indicating whether the
 *      product's quantity is zero.
 * - getFormattedPriceAttribute(): Returns the product price formatted to
 *      two decimal places as a string.
 * - hasBom(): Returns true (all products can have a BOM).
 * - getSumBomLineCosts(): Calculates the total cost of all BOM components.
 * - getTotalCostAttribute(): Returns BOM cost or price based on BOM presence.
 *
 * Example usage of accessors:
 * ```php
 * $product = Product::find(1);
 * $isActive = $product->is_active; // Check if the product is active
 * $isLowStock = $product->is_low_stock; // Check if stock is low
 * $isOutOfStock = $product->is_out_of_stock; // Check if stock is zero
 * $formattedPrice = $product->formatted_price; // e.g. "19.99"
 * $hasBom = $product->hasBom(); // Always true for products
 * $bomCost = $product->bomCost(); // Calculate total BOM cost
 * ```
 *
 * Query scopes include:
 * - scopeActive($query): Filter the query to only include active products.
 * - scopeDiscontinued($query): Filter the query to only include discontinued
 *      products.
 * - scopePending($query): Filter the query to only include pending products.
 * - scopeWithStatus($query, $status): Filter the query to only include
 *      products with a given status or list of statuses.
 * - scopeLowStock($query): Filter the query to only include products whose
 *      quantity is at or below their reorder point.
 * - scopeOutOfStock($query): Filter the query to only include products with
 *      a quantity of zero.
 * - scopeInCurrency($query, $currency): Filter the query to only include
 *      products priced in a given currency or list of currencies.
 * - scopeReal($query): Filter the query to only include non-test products.
 * - scopeSearch($query, $term): Filter the query by name, SKU, or
 *      description using a single search term.
 *
 * Example usage of query scopes:
 * ```php
 * $active = Product::active()->get(); // Get all active products
 * $lowStock = Product::lowStock()->get(); // Products below reorder point
 * $outOfStock = Product::outOfStock()->get(); // Products with zero stock
 * $gbpProducts = Product::inCurrency('GBP')->get(); // Products priced in GBP
 * $results = Product::search('widget')->get(); // Search by name or SKU
 * $real = Product::real()->get(); // Exclude test records
 * ```
 */
class Product extends Model
{
    /**
     * @use HasFactory<\Database\Factories\ProductFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasBillOfMaterials<App\Traits\BillOfMaterials\HasBillOfMaterials>
     * @use HasStockLevels<App\Traits\Products\HasStockLevels>
     */
    use HasFactory,
        SoftDeletes,
        HasBillOfMaterials,
        HasStockLevels;

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
     * Get the deals associated with the product.
     *
     * Includes pivot data such as quantity and price.
     *
     * @return BelongsToMany<Deal>
     */
    public function deals(): BelongsToMany
    {
        return $this->belongsToMany(
            Deal::class,
            'deal_products',
            'product_id',
            'deal_id'
        )
            ->using(DealProduct::class)
            ->withPivot(['quantity', 'price', 'total', 'deleted_at'])
            ->withTimestamps()
            ->whereNull('deal_products.deleted_at');
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
        return $this->belongsToMany(
            Quote::class,
            'quote_products',
            'product_id',
            'quote_id'
        )
            ->using(QuoteProduct::class)
            ->withPivot(['quantity', 'price', 'total', 'meta', 'deleted_at'])
            ->withTimestamps()
            ->whereNull('quote_products.deleted_at');
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
        return $this->belongsToMany(
            Order::class,
            'order_products',
            'product_id',
            'order_id'
        )
            ->using(OrderProduct::class)
            ->withPivot(['quantity', 'price', 'total', 'meta', 'deleted_at'])
            ->withTimestamps()
            ->whereNull('order_products.deleted_at');
    }

    /**
     * Get all stock movements for the product.
     *
     * @return HasMany<ProductStockMovement>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(ProductStockMovement::class);
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
     * Get the bill of materials entries where this product is the manufacturable
     * (parent assembly).
     *
     * This polymorphic relationship allows products to be assembled from parts.
     * Each BOM entry represents a component (child part) required to manufacture
     * this product.
     *
     * @return MorphMany<BillOfMaterial>
     */
    public function billOfMaterials(): MorphMany
    {
        return $this->morphMany(BillOfMaterial::class, 'manufacturable');
    }

    /**
     * Determine if the product is low on stock.
     *
     * Returns true when a reorder point is set and the current quantity
     * is at or below it.
     *
     * @return bool
     */
    public function getIsLowStock(): bool
    {
        return $this->reorder_point && $this->quantity <= $this->reorder_point;
    }

    /**
     * Determine if the product is out of stock.
     *
     * @return bool
     */
    public function getIsOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    /**
     * Determine whether the product has an active status.
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::ACTIVE_PRODUCT_STATUS;
    }

    /**
     * Determine whether the product has been discontinued.
     *
     * @return bool
     */
    public function getIsDiscontinuedAttribute(): bool
    {
        return $this->status === self::DISCONTINUED_PRODUCT_STATUS;
    }

    /**
     * Get the product price formatted to two decimal places.
     *
     * Returns the price as a string without currency symbols. Pair with
     * the product's currency field for a fully formatted display value.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 2, '.', '');
    }

    /**
     * Get the total cost of the product including BOM costs if applicable.
     *
     * Returns the calculated BOM cost if the product has bill of materials
     * entries, otherwise returns the product price.
     *
     * @return float
     */
    public function getTotalCostAttribute(): float
    {
        if ($this->hasBom()) {
            return $this->bomCost();
        }

        return $this->price;
    }

    /**
     * Determine whether this product can have an associated bill of materials.
     *
     * Always returns true for products, as all products support BOM entries.
     * Products can be assembled from parts regardless of other attributes.
     *
     * @return bool
     */
    public function hasBom(): bool
    {
        return true;
    }

    /**
     * Sum the total costs of all BOM line entries for this product.
     *
     * Traverses the BOM tree to calculate the sum of all component costs.
     * The $visited array prevents infinite loops in circular BOM structures.
     *
     * @param  array<int> $visited Product/Part IDs already visited in the current
     *                             traversal, passed through to prevent circular references.
     *
     * @return float The summed cost of all BOM lines.
     */
    public function getSumBomLineCosts(array $visited): float
    {
        return $this->billOfMaterials->sum(
            fn ($bom) => $bom->totalCost($visited) ?? 0
        );
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  Builder<Product> $query The query builder instance.
     *
     * @return Builder<Product> The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::ACTIVE_PRODUCT_STATUS);
    }

    /**
     * Scope a query to only include discontinued products.
     *
     * Useful for auditing or archiving workflows where discontinued
     * products must be reviewed separately from active inventory.
     *
     * @param  Builder<Product> $query The query builder instance.
     *
     * @return Builder<Product> The modified query builder instance.
     */
    public function scopeDiscontinued(Builder $query): Builder
    {
        return $query->where('status', self::DISCONTINUED_PRODUCT_STATUS);
    }

    /**
     * Scope a query to only include pending products.
     *
     * Pending products are those that have been created but not yet
     * approved or activated for sale.
     *
     * @param  Builder<Product> $query The query builder instance.
     *
     * @return Builder<Product> The modified query builder instance.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::PENDING_PRODUCT_STATUS);
    }

    /**
     * Scope a query to only include products with a given status or
     * list of statuses.
     *
     * Accepts either a single status string or an array of statuses.
     * Applies a where or whereIn clause accordingly. Values should match
     * one of the PRODUCT_STATUSES constants.
     *
     * @param  Builder<Product> $query The query builder instance.
     * @param  string|array<int,string> $status The status or statuses
     *                                          to filter by.
     *
     * @return Builder<Product> The modified query builder instance.
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
     * Scope a query to products that are low on stock.
     *
     * Includes only products that have a reorder point configured and
     * whose current quantity is at or below it. Products without a
     * reorder point are excluded from the results.
     *
     * @param  Builder<Product> $query The query builder instance.
     *
     * @return Builder<Product> The modified query builder instance.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereNotNull('reorder_point')
            ->whereColumn('quantity', '<=', 'reorder_point');
    }

    /**
     * Scope a query to only include products priced in a given currency
     * or list of currencies.
     *
     * Accepts either a single currency code as a string or an array of
     * currency codes. Applies a where or whereIn clause accordingly.
     *
     * @param  Builder<Product> $query The query builder instance.
     * @param  string|array<int,string> $currency  The currency code or
     *                                              codes to filter by.
     *
     * @return Builder<Product> The modified query builder instance.
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
     * Scope a query to only include non-test products.
     *
     * Filters out any product records where the 'is_test' flag is true,
     * ensuring that queries return only real product records. Important
     * for accurate inventory reporting and commercial workflows.
     *
     * @param  Builder<Product> $query The query builder instance.
     *
     * @return Builder<Product> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * Scope a query to search products by name, SKU, or description
     * using a single search term.
     *
     * Wraps the conditions in a grouped where clause to ensure correct
     * boolean precedence when chained with other scopes.
     *
     * @param  Builder<Product>  $query  The query builder instance.
     * @param  string $term The search term to match against.
     *
     * @return Builder<Product> The modified query builder instance.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = "%{$term}%";

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }
}
