<?php

namespace App\Services\Industries;

use App\Models\Industry;

/**
 * Handles soft deletion and restoration of Industry records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by,
 * and restored_at columns are always populated.
 */
class IndustryDestructorService
{
    /**
     * Soft-delete a industry.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the industry.
     *
     * @param  Industry $industry The industry to soft-delete.
     *
     * @return void
     */
    public function destroy(Industry $industry): void
    {
        $userId = auth()->id();

        $industry->update([
            'deleted_by' => $userId,
        ]);

        $industry->delete();
    }

    /**
     * Restore a soft-deleted industry.
     *
     * Looks up the industry including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the industry. Returns the industry unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted industry.
     *
     * @return Industry The restored industry instance.
     */
    public function restore(int $id): Industry
    {
        $userId = auth()->id();

        $industry = Industry::withTrashed()->findOrFail($id);

        if ($industry->trashed()) {
            $industry->update([
                'restored_by' => $userId,
            ]);
            $industry->restore();
        }

        return $industry;
    }
}
