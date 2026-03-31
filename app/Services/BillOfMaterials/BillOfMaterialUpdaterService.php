<?php

namespace App\Services\BillOfMaterials;

use App\Http\Requests\UpdateBillOfMaterialRequest;
use App\Models\BillOfMaterial;

/**
 * Handles updates to Bill of Materials (BOM) entries.
 *
 * Validates incoming data and applies updates to the BOM record,
 * returning the updated model with relevant relationships loaded.
 */
class BillOfMaterialUpdaterService
{
    /**
     * Update an existing BOM entry.
     *
     * Applies validated request data to the BOM record and reloads
     * the child part relationship.
     *
     * @param  UpdateBillOfMaterialRequest $request The request containing
     * validated update data.
     * @param  BillOfMaterial $billOfMaterial The BOM entry to update.
     *
     * @return BillOfMaterial The updated BOM entry with relationships loaded.
     */
    public function update(
        UpdateBillOfMaterialRequest $request,
        BillOfMaterial $billOfMaterial
    ): BillOfMaterial {
        $billOfMaterial->update($request->validated());

        return $billOfMaterial->load('childPart');
    }
}
