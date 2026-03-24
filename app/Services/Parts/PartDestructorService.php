<?php

namespace App\Services\Parts;

use App\Models\Part;

class PartDestructorService
{
    /**
     * Soft-delete a part.
     *
     * @param Part $part
     *
     * @return void
     */
    public function destroy(Part $part): void
    {
        $userId = auth()->id();

        $part->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $part->delete();
    }

    /**
     * Restore a trashed part.
     *
     * @param int $id
     *
     * @return Part
     */
    public function restore(int $id): Part
    {
        $userId = auth()->id();

        $part = Part::withTrashed()->findOrFail($id);

        if ($part->trashed()) {
            $part->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $part->restore();
        }

        return $part;
    }
}
