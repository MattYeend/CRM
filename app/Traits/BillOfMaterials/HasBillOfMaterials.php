<?php

namespace App\Traits\BillOfMaterials;

use App\Models\BillOfMaterial;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait for models that can have a Bill of Materials.
 *
 * Any model using this trait can act as a manufacturable entity
 * with components defined through BOM entries.
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
     * Calculate the total BOM cost for this entity, including sub-assemblies.
     *
     * Recursively traverses the bill of materials tree. Returns null if the
     * entity has no BOM entries. Circular references are prevented via the
     * visited array.
     *
     * @param  array $visited Entity IDs already visited in the current
     * traversal, used to prevent infinite recursion.
     *
     * @return float|null The total BOM cost, or null if unresolvable.
     */
    public function bomCost(array &$visited = []): ?float
    {
        $key = static::class . '-' . $this->id;

        if (in_array($key, $visited, true)) {
            return 0;
        }

        $visited[] = $key;

        $billOfMaterials = $this->billOfMaterials()->with('childPart')->get();

        if ($billOfMaterials->isEmpty()) {
            return null;
        }

        return $billOfMaterials->sum(function ($bom) use (&$visited) {
            $child = $bom->childPart;

            if (! $child) {
                return 0;
            }

            $scrapMultiplier = 1 + (($bom->scrap_percentage ?? 0) / 100);

            if ($child->hasBom()) {
                $childCost = $child->bomCost($visited);
                $childCost = $childCost ?? (float) ($child->cost_price ?? 0);
            } else {
                $childCost = (float) ($child->cost_price ?? 0);
            }

            return $bom->quantity * $scrapMultiplier * $childCost;
        });
    }
}
