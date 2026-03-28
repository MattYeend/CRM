<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartStockMovement extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PartStockMovementFactory>
     */
    use HasFactory;

    /**
     * Stock movement type constants.
     */
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_RETURN = 'return';

    /**
     * Human-readable labels for each movement type.
     *
     * Useful for dropdowns and display:
     *   PartStockMovement::TYPES  — all types
     *   Select::make()->options(PartStockMovement::TYPES) — Filament select
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
     * Part the movement belongs to.
     *
     * @return BelongsTo<Part,PartStockMovement>
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * The user that created the movement.
     *
     * @return BelongsTo<Part,PartStockMovement>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to movements of a given type.
     *
     * @param  Builder $query
     *
     * @param  string  $type  Movement type to filter by
     * (e.g. 'in', 'out', 'return')
     *
     * @return Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Determine whether this movement adds stock (type 'in' or 'return').
     *
     * @return bool
     */
    public function isInbound(): bool
    {
        return in_array($this->type, [self::TYPE_IN, self::TYPE_RETURN]);
    }

    /**
     * Determine whether this movement removes stock (type 'out').
     *
     * @return bool
     */
    public function isOutbound(): bool
    {
        return $this->type === self::TYPE_OUT;
    }
}
