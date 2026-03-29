<?php

namespace App\Services\BillOfMaterials;

use App\Models\Part;
use App\Models\BillOfMaterial;
use App\Http\Requests\StoreBillOfMaterialRequest;

class BillOfMaterialCreatorService
{
    /**
     * Create a new BOM entry.
     *
     * @param StoreBillOfMaterialRequest $request
     *
     * @param Part $part
     *
     * @return BillOfMaterial
     */
    public function create(StoreBillOfMaterialRequest $request, Part $part): BillOfMaterial
    {
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
