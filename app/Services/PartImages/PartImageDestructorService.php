<?php

namespace App\Services\PartImages;

use App\Models\PartImage;

class PartImageDestructorService
{
    /**
     * Soft-delete a part image.
     *
     * @param PartImage $partImage
     *
     * @return void
     */
    public function destroy(PartImage $partImage): void
    {
        $userId = auth()->id();

        $partImage->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $partImage->delete();
    }

    /**
     * Restore a trashed part image.
     *
     * @param int $id
     *
     * @return PartImage
     */
    public function restore(int $id): PartImage
    {
        $userId = auth()->id();

        $partImage = PartImage::withTrashed()->findOrFail($id);

        if ($partImage->trashed()) {
            $partImage->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $partImage->restore();
        }

        return $partImage;
    }
}
