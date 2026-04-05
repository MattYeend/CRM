<?php

namespace App\Services\PartSerialNumbers;

use App\Models\PartSerialNumber;

/**
 * Handles soft deletion and restoration of PartSerialNumber records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class PartSerialNumberDestructorService
{
    /**
     * Soft-delete a part serial number.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the part serial number.
     *
     * @param  PartSerialNumber $partSerialNumber The part serial number
     * instance to soft-delete.
     *
     * @return void
     */
    public function destroy(PartSerialNumber $partSerialNumber): void
    {
        $userId = auth()->id();

        $partSerialNumber->update([
            'deleted_by' => $userId,
        ]);

        $partSerialNumber->delete();
    }

    /**
     * Restore a soft-deleted part serial number.
     *
     * Looks up the part serial number including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the record. Returns the part serial number unchanged if it is not
     * currently trashed.
     *
     * @param  int $id The primary key of the soft-deleted part serial number.
     *
     * @return PartSerialNumber The restored part serial number instance.
     */
    public function restore(int $id): PartSerialNumber
    {
        $userId = auth()->id();

        $partSerialNumber = PartSerialNumber::withTrashed()->findOrFail($id);

        if ($partSerialNumber->trashed()) {
            $partSerialNumber->update([
                'restored_by' => $userId,
            ]);
            $partSerialNumber->restore();
        }

        return $partSerialNumber;
    }
}
