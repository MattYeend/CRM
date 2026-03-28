<?php

namespace App\Services\PartSerialNumbers;

use App\Models\PartSerialNumber;

class PartSerialNumberDestructorService
{
    /**
     * Soft-delete a part serial number.
     *
     * @param PartSerialNumber $partSerialNumber
     *
     * @return void
     */
    public function destroy(PartSerialNumber $partSerialNumber): void
    {
        $userId = auth()->id();

        $partSerialNumber->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $partSerialNumber->delete();
    }

    /**
     * Restore a trashed part serial number.
     *
     * @param int $id
     *
     * @return PartSerialNumber
     */
    public function restore(int $id): PartSerialNumber
    {
        $userId = auth()->id();

        $partSerialNumber = PartSerialNumber::withTrashed()->findOrFail($id);

        if ($partSerialNumber->trashed()) {
            $partSerialNumber->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $partSerialNumber->restore();
        }

        return $partSerialNumber;
    }
}
