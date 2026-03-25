<?php

namespace App\Services\PartCategories;

use App\Models\PartCategory;

class PartCategoryDestructorService
{
    /**
     * Soft-delete a partCategory.
     *
     * @param PartCategory $partCategory
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
