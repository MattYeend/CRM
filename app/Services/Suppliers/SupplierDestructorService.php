<?php

namespace App\Services\Suppliers;

use App\Models\Supplier;

class SupplierDestructorService
{
    /**
     * Soft-delete a supplier.
     *
     * @param Supplier $supplier
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
     * Restore a trashed supplier.
     *
     * @param int $id
     *
     * @return Supplier
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
