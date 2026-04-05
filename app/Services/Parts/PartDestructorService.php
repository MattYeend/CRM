<?php

namespace App\Services\Parts;

use App\Models\Part;

/**
 * Handles soft deletion and restoration of Part records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class PartDestructorService
{
    /**
     * Soft-delete a part.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the part.
     *
     * @param  Part $part The part instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Part $part): void
    {
        $userId = auth()->id();

        $part->update([
            'deleted_by' => $userId,
        ]);

        $part->delete();
    }

    /**
     * Restore a soft-deleted part.
     *
     * Looks up the part including trashed records, records the authenticated
     * user and timestamp in the audit columns, then restores the part.
     * Returns the part unchanged if it is not currently trashed.
     *
     * @param  int $id The primary key of the soft-deleted part.
     *
     * @return Part The restored part instance.
     */
    public function restore(int $id): Part
    {
        $userId = auth()->id();

        $part = Part::withTrashed()->findOrFail($id);

        if ($part->trashed()) {
            $part->update([
                'restored_by' => $userId,
            ]);
            $part->restore();
        }

        return $part;
    }
}
