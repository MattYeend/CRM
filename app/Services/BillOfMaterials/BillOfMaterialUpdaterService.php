<?php

namespace App\Services\BillOfMaterials;

use App\Http\Requests\UpdateBillOfMaterialRequest;
use App\Models\BillOfMaterial;

class BillOfMaterialUpdaterService
{
    /**
     * Update an existing BOM entry.
     *
     * @param UpdateBillOfMaterialRequest $request
     * @param BillOfMaterial $billOfMaterial
     *
     * @return BillOfMaterial
     */
    public function update(
        UpdateBillOfMaterialRequest $request,
        BillOfMaterial $billOfMaterial
    ): BillOfMaterial {
        $billOfMaterial->update($request->validated());
        return $billOfMaterial->load('childPart');
    }
}
