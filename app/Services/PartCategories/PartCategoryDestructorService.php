<?php

namespace App\Services\PartCategories;

use App\Models\PartCategory;

/**
 * Handles soft deletion and restoration of PartCategory records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class PartCategoryDestructorService
{
    /**
     * Soft-delete a part category.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the part category.
     *
     * @param  PartCategory $partCategory The part category instance to soft-delete.
     *
     * @return void
     */
    public function destroy(PartCategory $partCategory): void
    {
        $userId = auth()->id();

        $partCategory->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $partCategory->delete();
    }

    /**
     * Restore a trashed part.
     *
     * @param int $id
     *
     * @return PartCategory
     */
    public function restore(int $id): PartCategory
    {
        $userId = auth()->id();

        $partCategory = PartCategory::withTrashed()->findOrFail($id);

        if ($partCategory->trashed()) {
            $partCategory->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $partCategory->restore();
        }

        return $partCategory;
    }
}
