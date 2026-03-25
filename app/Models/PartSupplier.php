<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PartSupplier extends Pivot
{
    protected $table = 'part_suppliers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'part_id', 'supplier_id',
        'supplier_sku', 'unit_cost',
        'lead_time_days', 'is_preferred',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'unit_cost'    => 'decimal:2',
        'is_preferred' => 'boolean',
    ];

    /**
     * Part the supplier belongs to
     *
     * @return BelongsTo
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Supplier the part belongs to
     *
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
