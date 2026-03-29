<?php

namespace App\Services\BillOfMaterials;

use App\Models\BillOfMaterial;
use App\Http\Requests\UpdateBillOfMaterialRequest;

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
    public function update(UpdateBillOfMaterialRequest $request, BillOfMaterial $billOfMaterial): BillOfMaterial
    {
        $billOfMaterial->update($request->validated());
        return $billOfMaterial->load('childPart');
    }
}
