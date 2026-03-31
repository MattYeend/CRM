<?php

namespace App\Services\BillOfMaterials;

use App\Models\BillOfMaterial;

/**
 * Handles soft deletion and restoration of Bill of Materials (BOM) entries.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by,
 * and restored_at columns are always populated.
 */
class BillOfMaterialDestructorService
{
    /**
     * Soft-delete a BOM entry.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the BOM entry.
     *
     * @param  BillOfMaterial $billOfMaterial The BOM entry to soft-delete.
     *
     * @return void
     */
    public function delete(BillOfMaterial $billOfMaterial): void
    {
        $userId = auth()->id();

        $billOfMaterial->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $billOfMaterial->delete();
    }

    /**
     * Restore a soft-deleted BOM entry.
     *
     * Looks up the BOM entry including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the BOM entry. Returns the BOM unchanged if it is not currently trashed.
     *
     * @param  int $id The primary key of the soft-deleted BOM entry.
     *
     * @return BillOfMaterial The restored BOM entry instance.
     */
    public function restore(int $id): BillOfMaterial
    {
        $userId = auth()->id();

        $billOfMaterial = BillOfMaterial::withTrashed()->findOrFail($id);

        if ($billOfMaterial->trashed()) {
            $billOfMaterial->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $billOfMaterial->restore();
        }

        return $billOfMaterial;
    }
}
