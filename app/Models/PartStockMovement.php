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
}
