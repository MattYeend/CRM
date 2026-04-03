<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model representing the many-to-many relationship between parts
 * and suppliers.
 *
 * Stores supplier-specific pricing, SKU, lead time, and preferred status
 * for each part-supplier pairing, along with standard audit and soft-delete
 * tracking columns.
 *
 * Relationships defined in this model include:
 * - part(): Belongs-to relationship to the Part this pivot entry belongs to.
 * - supplier(): Belongs-to relationship to the Supplier associated with
 *      this pivot entry.
 * - creator(): Belongs-to relationship to the User who created the record.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      record.
 * - deleter(): Belongs-to relationship to the User who deleted the record
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the record
 *      (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $pivot = PartSupplier::find(1);
 * $part = $pivot->part; // Get the associated part
 * $supplier = $pivot->supplier; // Get the associated supplier
 * $creator = $pivot->creator; // Get the user that created the record
 * $updater = $pivot->updater; // Get the user that last updated the record
 * $deleter = $pivot->deleter; // Get the user that deleted the record
 * (if applicable)
 * $restorer = $pivot->restorer; // Get the user that restored the record
 * (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getFormattedUnitCostAttribute(): Returns the unit cost formatted to
 *      two decimal places as a string.
 * - getIsPreferredAttribute(): Returns a boolean indicating whether this
 *      supplier is the preferred source for the associated part.
 * Example usage of accessors:
 * ```php
 * $pivot = PartSupplier::find(1);
 * $cost = $pivot->formatted_unit_cost; // e.g. "12.50"
 * $isPreferred = $pivot->is_preferred; // Check if preferred supplier
 * ```
 *
 * Query scopes include:
 * - scopePreferred($query): Filter the query to only include preferred
 *      supplier records.
 * - scopeForPart($query, $partId): Filter the query to only include records
 *      for a given part.
 * - scopeForSupplier($query, $supplierId): Filter the query to only include
 *      records for a given supplier.
 * - scopeReal($query): Filter the query to only include non-test records.
 * Example usage of query scopes:
 * ```php
 * $preferred = PartSupplier::preferred()->get(); // Preferred entries only
 * $partLinks = PartSupplier::forPart($partId)->get(); // All suppliers for a
 * part
 * $supplierLinks = PartSupplier::forSupplier($id)->get(); // All parts for a
 * supplier
 * $real = PartSupplier::real()->get(); // Exclude test records
 * ```
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

    /**
     * Get the user that created the record.
     *
     * @return BelongsTo<User,PartSupplier>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the record.
     *
     * @return BelongsTo<User,PartSupplier>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the record.
     *
     * @return BelongsTo<User,PartSupplier>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the record.
     *
     * @return BelongsTo<User,PartSupplier>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the unit cost formatted to two decimal places.
     *
     * Returns the cost as a string without currency symbols. Pair with the
     * associated supplier's currency field for a fully formatted display
     * value suitable for procurement reports or purchase order previews.
     *
     * @return string
     */
    public function getFormattedUnitCostAttribute(): string
    {
        return number_format((float) $this->unit_cost, 2, '.', '');
    }

    /**
     * Get the total cost for a given quantity at the stored unit cost.
     *
     * Multiplies the provided quantity by the unit cost to calculate an
     * estimated line total. Useful for procurement planning without needing
     * to load the full order model.
     *
     * @param  int $quantity The number of units to cost.
     *
     * @return float
     */
    public function totalCostFor(int $quantity): float
    {
        return $quantity * (float) $this->unit_cost;
    }

    /**
     * Scope a query to only include preferred supplier records.
     *
     * Filters to entries where 'is_preferred' is true. Each part should
     * ideally have only one preferred supplier, making this scope useful
     * for resolving the default source during purchase order generation.
     *
     * @param  Builder<PartSupplier> $query    query builder instance.
     *
     * @return Builder<PartSupplier> The modified query builder instance.
     */
    public function scopePreferred(Builder $query): Builder
    {
        return $query->where('is_preferred', true);
    }

    /**
     * Scope a query to only include records for a given part.
     *
     * Filters by the 'part_id' column. Useful for listing all suppliers
     * that can provide a specific part without going through the Part model's
     * relationship, for example in a supplier comparison view.
     *
     * @param  Builder<PartSupplier> $query The query builder instance.
     * @param  int $partId    ID of the part to filter by.
     *
     * @return Builder<PartSupplier> The modified query builder instance.
     */
    public function scopeForPart(
        Builder $query,
        int $partId
    ): Builder {
        return $query->where('part_id', $partId);
    }

    /**
     * Scope a query to only include records for a given supplier.
     *
     * Filters by the 'supplier_id' column. Useful for listing all parts
     * that a specific supplier can provide, for example in a supplier
     * catalogue or onboarding review.
     *
     * @param  Builder<PartSupplier> $query The query builder instance.
     * @param  int $supplierId  The ID of the supplier to filter by.
     *
     * @return Builder<PartSupplier> The modified query builder instance.
     */
    public function scopeForSupplier(
        Builder $query,
        int $supplierId
    ): Builder {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope a query to only include non-test records.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring
     * that queries return only real part-supplier associations. Important
     * for accurate procurement reporting and cost analysis.
     *
     * @param  Builder<PartSupplier> $query The query builder instance.
     *
     * @return Builder<PartSupplier> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
