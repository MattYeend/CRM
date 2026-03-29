<?php

namespace App\Services\BillOfMaterials;

use App\Models\Part;
use App\Models\BillOfMaterial;
use App\Http\Requests\StoreBillOfMaterialRequest;
use App\Http\Requests\UpdateBillOfMaterialRequest;

class BillOfMaterialManagementService
{
    private BillOfMaterialCreatorSerivce $creator;
    private BillOfMaterialUpdaterService $updater;
    private BillOfMaterialDestructorService $destructor;

    public function __construct(
        BillOfMaterialCreatorSerivce $creator,
        BillOfMaterialUpdaterService $updater,
        BillOfMaterialDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new BOM entry.
     *
     * @param StoreBillOfMaterialRequest $request
     * @param Part $part
     *
     * @return array
     */
    public function store(StoreBillOfMaterialRequest $request, Part $part): array
    {
        return $this->creator->create($request, $part);
    }

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
        BillOfMaterial $billOfMaterial,
    ): BillOfMaterial {
        return $this->updater->update($request, $billOfMaterial);
    }

    /**
     * Delete a BOM entry.
     *
     * @param BillOfMaterial $billOfMaterial
     *
     * @return void
     */
    public function destroy(BillOfMaterial $billOfMaterial): void
    {
        $this->destructor->delete($billOfMaterial);
    }

    /**
     * Restore a soft-deleted BOM entry.
     *
     * @param int $id
     *
     * @return BillOfMaterial
     */
    public function restore(int $id): BillOfMaterial
    {
        return $this->destructor->restore($id);
    }
}
