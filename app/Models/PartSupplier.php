<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model representing the many-to-many relationship between parts
 * and suppliers.
 *
 * Stores supplier-specific pricing, SKU, lead time, and preferred status
 * for each part-supplier pairing, along with standard audit and soft-delete
 * tracking columns.
 */
class PartSupplier extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'part_suppliers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'part_id',
        'supplier_id',
        'supplier_sku',
        'unit_cost',
        'lead_time_days',
        'is_preferred',
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
        'unit_cost' => 'decimal:2',
        'is_preferred' => 'boolean',
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the part this supplier entry belongs to.
     *
     * @return BelongsTo<Part,PartSupplier>
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Get the supplier associated with this pivot entry.
     *
     * @return BelongsTo<Supplier,PartSupplier>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
