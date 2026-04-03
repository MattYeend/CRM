<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a single line in a Bill of Materials, defining the relationship
 * between a parent (manufactured) part and a child (component) part.
 *
 * Tracks the required quantity, scrap allowance, and unit of measure for each
 * component, and provides methods for calculating both direct and recursive
 * assembly costs.
 *
 * Relationships defined in this model include:
 * - parentPart(): The manufactured part that requires the component.
 * - childPart(): The component part that is consumed by the parent.
 * - creator(): The user that created the BOM entry.
 * - updater(): The user that last updated the BOM entry.
 * - deleter(): The user that deleted the BOM entry (if soft-deleted).
 * - restorer(): The user that restored the BOM entry (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $bomEntry = BillOfMaterial::find(1);
 * $parent = $bomEntry->parentPart; // Get the parent part
 * $child = $bomEntry->childPart; // Get the child part
 * $creator = $bomEntry->creator; // Get the user that created this
 *      BOM entry
 * $updater = $bomEntry->updater; // Get the user that last updated
 *      this BOM entry
 * $deleter = $bomEntry->deleter; // Get the user that deleted this
 *      BOM entry (if applicable)
 * $restorer = $bomEntry->restorer; // Get the user that restored this
 *      BOM entry (if applicable)
 * ```
 *
 * Accessor methods include:
 * - effectiveQuantity(): Calculates the quantity required including
 *      scrap allowance.
 * - lineCost(): Calculates the direct cost for this BOM entry based
 *      on the child part's cost price.
 * - totalCost(): Recursively calculates the total cost for this BOM
 *      entry, including all sub-assemblies.
 * Example usage of accessors:
 * ```php
 * $bomEntry = BillOfMaterial::find(1);
 * $effectiveQty = $bomEntry->effectiveQuantity(); // Get the quantity
 *  including scrap
 * $lineCost = $bomEntry->lineCost(); // Get the direct line cost for
 *  this BOM entry
 * $totalCost = $bomEntry->totalCost(); // Get the total cost including
 *  sub-assemblies
 * ```
 *
 * Query scopes include:
 * - scopeForParentPart($query, $partId): Filter BOM entries by parent part ID.
 * - scopeForChildPart($query, $partId): Filter BOM entries by child part ID.
 * - scopeTestEntries($query): Filter BOM entries that are marked as test data.
 * - scopeReal($query): Filter BOM entries that are not marked as test data.
 * Example usage of query scopes:
 * ```php
 * $parentBOMs = BillOfMaterial::forParentPart($parentId)->get(); // Get BOM
 *  entries for a specific parent part
 * $childBOMs = BillOfMaterial::forChildPart($childId)->get(); // Get BOM
 *  entries for a specific child part
 * $testBOMs = BillOfMaterial::testEntries()->get(); // Get BOM entries that
 *  are marked as test data
 * $realBOMs = BillOfMaterial::real()->get(); // Get BOM entries that are not
 *  marked as test data
 * ```
 */
class BillOfMaterial extends Model
{
    /**
     * @use HasFactory<\Database\Factories\BillOfMaterialFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'parent_part_id',
        'child_part_id',
        'quantity',
        'scrap_percentage',
        'unit_of_measure',
        'notes',
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
        'meta' => 'array',
        'is_test' => 'boolean',
        'quantity' => 'decimal:4',
        'scrap_percentage' => 'decimal:2',
    ];

    /**
     * Get the parent part that this BOM entry belongs to.
     *
     * The parent is the manufactured part that requires child components.
     *
     * @return BelongsTo<Part,BillOfMaterial>
     */
    public function parentPart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'parent_part_id');
    }

    /**
     * Get the child part (component) consumed by the parent part.
     *
     * @return BelongsTo<Part,BillOfMaterial>
     */
    public function childPart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'child_part_id');
    }

    /**
     * Get the user that created the bill of material.
     *
     * @return BelongsTo<User,BillOfMaterial>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the bill of material.
     *
     * @return BelongsTo<User,BillOfMaterial>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the bill of material.
     *
     * @return BelongsTo<User,BillOfMaterial>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the bill of material.
     *
     * @return BelongsTo<User,BillOfMaterial>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Calculate the required quantity adjusted for the scrap allowance.
     *
     * Adds the scrap percentage on top of the base quantity to account for
     * material loss during manufacturing.
     *
     * @return float The quantity including scrap.
     */
    public function effectiveQuantity(): float
    {
        $scrap = (float) ($this->scrap_percentage ?? 0);

        return (float) $this->quantity * (1 + ($scrap / 100));
    }

    /**
     * Calculate the direct line cost for this BOM entry.
     *
     * Multiplies the effective quantity by the child part's cost price.
     * Returns null if the child part has no cost price set.
     *
     * @return float|null The direct cost, or null if cost price is unavailable.
     */
    public function lineCost(): ?float
    {
        $cost = $this->childPart?->cost_price;

        if ($cost === null) {
            return null;
        }

        return $this->effectiveQuantity() * (float) $cost;
    }

    /**
     * Calculate the total cost for this BOM entry, including sub-assemblies.
     *
     * Recursively traverses the bill of materials tree to sum costs at all
     * levels. Circular references are prevented via the visited array.
     * Returns null if the child part cannot be resolved, and zero if a
     * circular reference is detected.
     *
     * @param  array $visited Part IDs already visited in the current traversal,
     * used to prevent infinite recursion.
     *
     * @return float|null The total recursive cost, or null if unresolvable.
     */
    public function totalCost(array $visited = []): ?float
    {
        if (! $this->childPart) {
            return null;
        }

        if (in_array($this->child_part_id, $visited)) {
            return 0;
        }

        $visited[] = $this->child_part_id;

        if ($this->childPart->hasBom()) {
            return $this->effectiveQuantity()
                * ($this->childPart->bomCost($visited) ?? 0);
        }

        return $this->lineCost();
    }

    /**
     * Scope a query to only include BOM entries for a specific parent part.
     *
     * @param  Builder<BillOfMaterial> $query The query builder instance.
     * @param  int $partId The ID of the parent part to filter by.
     *
     * @return Builder<BillOfMaterial> The modified query builder instance.
     */
    public function scopeForParentPart($query, int $partId): Builder
    {
        return $query->where('parent_part_id', $partId);
    }

    /**
     * Scope a query to only include BOM entries for a specific child part.
     *
     * @param  Builder<BillOfMaterial> $query The query builder instance.
     * @param  int $partId The ID of the child part to filter by.
     *
     * @return Builder<BillOfMaterial> The modified query builder instance.
     */
    public function scopeForChildPart($query, int $partId): Builder
    {
        return $query->where('child_part_id', $partId);
    }

    /**
     * Scope a query to only include BOM entries marked as test data.
     *
     * @param  Builder<BillOfMaterial> $query The query builder instance.
     *
     * @return Builder<BillOfMaterial> The modified query builder instance.
     */
    public function scopeTestEntries($query): Builder
    {
        return $query->where('is_test', true);
    }

    /**
     * Scope a query to exlude test records.
     *
     * This scope filters the query to include only users where the
     * 'is_test' attribute is false, effectively excluding any users
     * that are marked as test records. This is useful for ensuring
     * that queries return only real user records in the system.
     *
     * @param  Builder<BillOfMaterial> $query The query builder instance.
     *
     * @return Builder<BillOfMaterial> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
