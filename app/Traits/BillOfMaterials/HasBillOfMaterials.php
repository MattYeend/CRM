<?php

namespace App\Traits\BillOfMaterials;

use App\Models\BillOfMaterial;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait for models that can have a Bill of Materials.
 *
 * Provides relationships and cost calculation helpers for any manufacturable
 * entity (e.g. Part, Product) that participates in a BOM structure.
 */
trait HasBillOfMaterials
{
    /**
     * Get the bill of materials entries where this entity is the parent.
     *
     * @return MorphMany<BillOfMaterial>
     */
    public function billOfMaterials(): MorphMany
    {
        return $this->morphMany(BillOfMaterial::class, 'manufacturable');
    }

    /**
     * Determine whether this entity has an associated bill of materials.
     *
     * @return bool
     */
    public function hasBom(): bool
    {
        return $this->billOfMaterials()->exists();
    }

    /**
     * Calculate the UNIT BOM cost of this entity.
     *
     * This traverses the BOM tree recursively but does NOT apply quantities.
     * It represents the cost of producing one unit of this entity.
     *
     * Scrap percentages are applied at each level.
     *
     * @param  array $visited Prevents circular references in recursive BOMs.
     * @return float
     */
    public function bomUnitCost(array $visited = []): float
{
    $key = $this->id;

    if (isset($visited[$key])) {
        return 0;
    }

    $visited[$key] = true;

    $lines = $this->billOfMaterials()->with('childPart')->get();

    if ($lines->isEmpty()) {
        return (float) ($this->cost_price ?? 0);
    }

    return $lines->sum(function ($bom) use ($visited) {
        $child = $bom->childPart;

        if (!$child) {
            return 0;
        }

        $scrapMultiplier = 1 + (($bom->scrap_percentage ?? 0) / 100);

        return $scrapMultiplier * $child->bomUnitCost($visited);
    });
}

    /**
     * Calculate the TOTAL BOM cost of this entity.
     *
     * Applies BOM quantities ONCE at the top level only.
     * This prevents recursive multiplication inflation.
     *
     * @param  array $visited Prevents circular references in recursive BOMs.
     * @return float
     */
    public function bomCost(array $visited = []): float
{
    return $this->billOfMaterials()
        ->with('childPart')
        ->get()
        ->sum(function ($bom) {
            $child = $bom->childPart;

            if (!$child) {
                return 0;
            }

            return $bom->quantity * $child->bomUnitCost([]);
        });
}
}
