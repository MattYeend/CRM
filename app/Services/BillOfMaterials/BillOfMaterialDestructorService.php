<?php

namespace App\Services\BillOfMaterials;

use App\Models\BillOfMaterial;

class BillOfMaterialDestructorService
{
    /**
     * Delete a BOM entry.
     *
     * @param BillOfMaterial $billOfMaterial
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
     * Restore a trashed BOM.
     *
     * @param int $id
     *
     * @return BillOfMaterial
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
