<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a single stock movement event for a part.
 *
 * Tracks the type of movement (in, out, adjustment, transfer, or return),
 * the quantity changed, and the stock levels before and after the movement.
 * Provides scopes and helper methods for filtering and classifying movements
 * by direction.
 *
 * Relationships defined in this model include:
 * - part(): Belongs-to relationship to the Part this stock movement belongs to.
 * - createdBy(): Belongs-to relationship to the User that created the stock
 *     movement record.
 * Example usage of relationships:
 * ```php
 * $movement = PartStockMovement::find(1);
 * $part = $movement->part; // Get the associated part
 * $creator = $movement->createdBy; // Get the user that created this movement
 * ```
 *
 * Helper methods include:
 * - isInbound(): Returns true if the movement type is 'in' or 'return',
 *      indicating it adds stock.
 * - isOutbound(): Returns true if the movement type is 'out', indicating it
 *      removes stock.
 * Example usage of helper methods:
 * ```php
 * $movement = PartStockMovement::find(1);
 * if ($movement->isInbound()) {
 *   // This movement adds stock
 * } elseif ($movement->isOutbound()) {
 *  // This movement removes stock
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
 * - scopeForPart($query, $partId): Filter the query to only include
 *      movements for a specific part ID.
 * - scopeReal($query): Filter the query to only include non-test movements.
 * Example usage of query scopes:
 * ```php
 * $inboundMovements = PartStockMovement::inbound()->get(); // Get all
 * inbound movements
 * $outboundMovements = PartStockMovement::outbound()->get(); // Get all
 * outbound movements
 * $partMovements = PartStockMovement::forPart($partId)->get(); // Get all
 * movements for a specific part
 * $inMovements = PartStockMovement::ofType(PartStockMovement::TYPE_IN)->get();
 * // Get all 'in' movements
 * $realMovements = PartStockMovement::real()->get(); // Get all non-test
 * movements
 * ```
 */
class PartStockMovement extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PartStockMovementFactory>
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
        'part_id',
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
     * Get the part this stock movement belongs to.
     *
     * @return BelongsTo<Part,PartStockMovement>
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Get the user that created the stock movement.
     *
     * @return BelongsTo<User,PartStockMovement>
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
    public function isInbound(): bool
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
    public function isOutbound(): bool
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
     * Scope a query to only include movements of a specific part.
     *
     * @param  Builder $query The query builder instance.
     * @param  int $partId The ID of the part to filter by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeForPart(Builder $query, int $partId): Builder
    {
        return $query->where('part_id', $partId);
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
