<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a single stock movement event for a product.
 *
 * Tracks the type of movement (in, out, adjustment, transfer, or return),
 * the quantity changed, and the stock levels before and after the movement.
 * Provides scopes and helper methods for filtering and classifying movements
 * by direction.
 *
 * Relationships defined in this model include:
 * - product(): Belongs-to relationship to the Product this stock movement belongs to.
 * - createdBy(): Belongs-to relationship to the User that created the stock
 *     movement record.
 * Example usage of relationships:
 * ```php
 * $movement = ProductStockMovement::find(1);
 * $product = $movement->product; // Get the associated product
 * $creator = $movement->createdBy; // Get the user that created this movement
 * ```
 *
 * Helper methods include:
 * - getIsInbound(): Returns true if the movement type is 'in' or 'return',
 *      indicating it adds stock.
 * - getIsOutbound(): Returns true if the movement type is 'out', indicating it
 *      removes stock.
 * Example usage of helper methods:
 * ```php
 * $movement = ProductStockMovement::find(1);
 * if ($movement->is_inbound) {
 *  // This movement adds stock
 * } elseif ($movement->is_outbound) {
 * // This movement removes stock
 * }
 * ```
 *
 * Query scopes include:
 * - scopeOfType($query, $type): Filter movements by a specific type (e.g. 'in',
 *      'out', 'return').
 * - scopeInbound($query): Filter the query to only include inbound movements
 *      (types 'in' and 'return').
 * - scopeOutbound($query): Filter the query to only include outbound movements
 *      (type 'out').
 * - scopeForProduct($query, $productId): Filter the query to only include
 *      movements for a specific product ID.
 * - scopeReal($query): Filter the query to only include non-test movements.
 * Example usage of query scopes:
 * ```php
 * $inboundMovements = ProductStockMovement::inbound()->get(); // Get all
 * inbound movements
 * $outboundMovements = ProductStockMovement::outbound()->get(); // Get all
 * outbound movements
 * $productMovements = ProductStockMovement::forProduct($productId)->get(); // Get all
 * movements for a specific product
 * $inMovements = ProductStockMovement::ofType(ProductStockMovement::TYPE_IN)->get();
 * // Get all 'in' movements
 * $realMovements = ProductStockMovement::real()->get(); // Get all non-test
 * movements
 * ```
 */
class ProductStockMovement extends Model
{
    /**
     * @use HasFactory<\Database\Factories\ProductStockMovementFactory>
     */
    use HasFactory;

    /**
     * Stock inbound movement type.
     */
    public const TYPE_IN = 'in';

    /**
     * Stock outbound movement type.
     */
    public const TYPE_OUT = 'out';

    /**
     * Stock adjustment movement type.
     */
    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * Stock transfer movement type.
     */
    public const TYPE_TRANSFER = 'transfer';

    /**
     * Stock return movement type.
     */
    public const TYPE_RETURN = 'return';

    /**
     * Human-readable labels for each movement type.
     *
     * Keyed by the type constant value, suitable for use in dropdowns and
     * display contexts (e.g. Filament select options).
     */
    public const TYPES = [
        self::TYPE_IN => 'In',
        self::TYPE_OUT => 'Out',
        self::TYPE_ADJUSTMENT => 'Adjustment',
        self::TYPE_TRANSFER => 'Transfer',
        self::TYPE_RETURN => 'Return',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference',
        'notes',
        'meta',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'meta' => 'array',
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
    ];

    /**
     * Get the product this stock movement belongs to.
     *
     * @return BelongsTo<Product,ProductStockMovement>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that created the stock movement.
     *
     * @return BelongsTo<User,ProductStockMovement>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Determine whether this movement adds stock.
     *
     * Returns true for movements of type 'in' or 'return'.
     *
     * @return bool
     */
    public function getIsInbound(): bool
    {
        return in_array($this->type, [self::TYPE_IN, self::TYPE_RETURN]);
    }

    /**
     * Determine whether this movement removes stock.
     *
     * Returns true for movements of type 'out'.
     *
     * @return bool
     */
    public function getIsOutbound(): bool
    {
        return $this->type === self::TYPE_OUT;
    }

    /**
     * Scope a query to movements of a given type.
     *
     * @param  Builder $query The query builder instance.
     *
     * @param  string $type The movement type to filter by (e.g. 'in', 'out',
     * 'return').
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include inbound movements.
     *
     * @param  Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeInbound(Builder $query): Builder
    {
        return $query->whereIn('type', [self::TYPE_IN, self::TYPE_RETURN]);
    }

    /**
     * Scope a query to only include outbound movements.
     *
     * @param  Builder $query The query builder instance.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeOutbound(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_OUT);
    }

    /**
     * Scope a query to only include movements of a specific product.
     *
     * @param  Builder $query The query builder instance.
     * @param  int $productId The ID of the product to filter by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include non-test movements.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring that
     * only real production data is included in the results.
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
