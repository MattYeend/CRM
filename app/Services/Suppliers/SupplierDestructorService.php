<?php

namespace App\Services\Suppliers;

use App\Models\Supplier;

/**
 * Handles soft deletion and restoration of Supplier records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class SupplierDestructorService
{
    /**
     * Soft-delete a supplier.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the supplier.
     *
     * @param  Supplier $supplier The supplier instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Supplier $supplier): void
    {
        $userId = auth()->id();

        $supplier->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $supplier->delete();
    }

    /**
     * Restore a soft-deleted supplier.
     *
     * Looks up the supplier including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the supplier. Returns the supplier unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted supplier.
     *
     * @return Supplier The restored supplier instance.
     */
    public function restore(int $id): Supplier
    {
        $userId = auth()->id();

        $supplier = Supplier::withTrashed()->findOrFail($id);

        if ($supplier->trashed()) {
            $supplier->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $supplier->restore();
        }

        return $supplier;
    }
}
