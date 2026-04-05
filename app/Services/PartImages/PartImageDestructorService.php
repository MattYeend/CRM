<?php

namespace App\Services\PartImages;

use App\Models\PartImage;

/**
 * Handles soft deletion and restoration of PartImage records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class PartImageDestructorService
{
    /**
     * Soft-delete a part image.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the part image.
     *
     * @param  PartImage $partImage The part image instance to soft-delete.
     *
     * @return void
     */
    public function destroy(PartImage $partImage): void
    {
        $userId = auth()->id();

        $partImage->update([
            'deleted_by' => $userId,
        ]);

        $partImage->delete();
    }

    /**
     * Restore a soft-deleted part image.
     *
     * Looks up the part image including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the part image. Returns the part image unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted part image.
     *
     * @return PartImage The restored part image instance.
     */
    public function restore(int $id): PartImage
    {
        $userId = auth()->id();

        $partImage = PartImage::withTrashed()->findOrFail($id);

        if ($partImage->trashed()) {
            $partImage->update([
                'restored_by' => $userId,
            ]);
            $partImage->restore();
        }

        return $partImage;
    }
}
