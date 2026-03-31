<?php

namespace App\Services\BillOfMaterials;

use App\Http\Requests\StoreBillOfMaterialRequest;
use App\Models\BillOfMaterial;
use App\Models\Part;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Handles creation of Bill of Materials (BOM) entries.
 *
 * Validates business rules before creating a new BOM entry, ensuring
 * that only manufactured parts can have child parts and that a part
 * cannot contain itself.
 */
class BillOfMaterialCreatorService
{
    /**
     * Create a new BOM entry.
     *
     * Validates the request and parent part, then creates a new
     * BillOfMaterial record linking the parent and child parts.
     *
     * @param  StoreBillOfMaterialRequest $request The validated request
     * data for creating the BOM.
     * @param  Part $part The parent part to which the child part will be added.
     *
     * @return BillOfMaterial The newly created BOM entry with loaded relations.
     *
     * @throws HttpResponseException If the parent part is not manufactured
     *         or if the part attempts to contain itself.
     */
    public function create(
        StoreBillOfMaterialRequest $request,
        Part $part
    ): BillOfMaterial {
        if (! $part->is_manufactured) {
            abort(422, 'This part is not manufactured.');
        }

        if ($request->child_part_id === $part->id) {
            abort(422, 'A part cannot contain itself.');
        }

        $billOfMaterial = BillOfMaterial::create([
            ...$request->validated(),
            'parent_part_id' => $part->id,
        ]);

        return $billOfMaterial->load('childPart', 'parentPart');
    }
}
