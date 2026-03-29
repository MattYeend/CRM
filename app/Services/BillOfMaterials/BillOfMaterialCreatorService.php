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
     * @param Part $part
     *
     * @return array
     */
    public function create(StoreBillOfMaterialRequest $request, Part $part): array
    {
        if (!$part->is_manufactured) {
            return ['error' => 'This part is not manufactured.'];
        }

        if ($request->child_part_id === $part->id) {
            return ['error' => 'A part cannot contain itself.'];
        }

        $bom = BillOfMaterial::create([
            ...$request->validated(),
            'parent_part_id' => $part->id,
        ]);

        return ['bom' => $bom->load('childPart')];
    }
}
