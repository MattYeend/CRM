<?php

namespace App\Services\BillOfMaterials;

use App\Http\Requests\StoreBillOfMaterialRequest;
use App\Http\Requests\UpdateBillOfMaterialRequest;
use App\Models\BillOfMaterial;
use Illuminate\Database\Eloquent\Model;

/**
 * Central service for managing Bill of Materials (BOM) entries.
 *
 * Delegates creation, update, deletion, and restoration operations to
 * the respective creator, updater, and destructor services, providing
 * a unified interface for BOM management.
 */
class BillOfMaterialManagementService
{
    private BillOfMaterialCreatorService $creator;
    private BillOfMaterialUpdaterService $updater;
    private BillOfMaterialDestructorService $destructor;

    public function __construct(
        BillOfMaterialCreatorService $creator,
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
     * Delegates to the creator service to validate and store the new BOM.
     *
     * @param  StoreBillOfMaterialRequest $request The request containing
     * BOM data.
     * @param  Model $manufacturable The parent part to which the BOM
     * will be attached.
     *
     * @return BillOfMaterial The newly created BOM entry.
     */
    public function store(
        StoreBillOfMaterialRequest $request,
        Model $manufacturable
    ): BillOfMaterial {
        return $this->creator->create($request, $manufacturable);
    }

    /**
     * Update an existing BOM entry.
     *
     * Delegates to the updater service to modify the BOM data.
     *
     * @param  UpdateBillOfMaterialRequest $request The request
     * containing updated BOM data.
     * @param  BillOfMaterial $billOfMaterial The BOM entry to update.
     *
     * @return BillOfMaterial The updated BOM entry.
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
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  BillOfMaterial $billOfMaterial The BOM entry to delete.
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
     * Delegates to the destructor service to restore the BOM.
     *
     * @param  int $id The primary key of the soft-deleted BOM entry.
     *
     * @return BillOfMaterial The restored BOM entry.
     */
    public function restore(int $id): BillOfMaterial
    {
        return $this->destructor->restore($id);
    }
}
