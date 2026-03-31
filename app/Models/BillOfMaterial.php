<?php

namespace App\Models;

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
}
