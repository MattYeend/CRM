<?php

namespace App\Services\PartImages;

use App\Models\PartImage;
use Illuminate\Support\Facades\Storage;

/**
 * Handles soft-deletion and restoration of PartImage records.
 *
 * Optionally deletes associated image files on soft-delete and restores
 * them on restore if they still exist in storage.
 */
class PartImageDestructorService
{
    /**
     * Soft-delete a part image.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the image.
     *
     * @param  PartImage $partImage The part image to soft-delete.
     *
     * @return void
     */
    public function destroy(PartImage $partImage): void
    {
        $userId = auth()->id();
        $partImage->update([
            'deleted_by' => $userId
        ]);

        $partImage->delete();
    }

    /**
     * Restore a soft-deleted part image.
     *
     * Restores the part image record from soft-delete state.
     *
     * @param  int $id The primary key of the soft-deleted part image.
     *
     * @return PartImage The restored part image instance.
     */
    public function restore(int $id): PartImage
    {
        $partImage = PartImage::withTrashed()->findOrFail($id);
        $userId = auth()->id();
        $partImage->update([
            'restored_by' => $userId,
            'restored_at' => now(),
        ]);

        $partImage->restore();

        return $partImage->fresh();
    }

    /**
     * Permanently delete a part image and its associated files.
     *
     * This is a hard delete that removes the database record and deletes
     * the image files from storage. Use with caution.
     *
     * @param  PartImage $partImage The part image to permanently delete.
     *
     * @return void
     */
    public function forceDestroy(PartImage $partImage): void
    {
        // Delete image files
        $this->deleteImageFiles($partImage);

        // Permanently delete the record
        $partImage->forceDelete();
    }

    /**
     * Delete the image and thumbnail files for a part image.
     *
     * @param  PartImage $partImage The part image whose files should be deleted.
     *
     * @return void
     */
    private function deleteImageFiles(PartImage $partImage): void
    {
        if ($partImage->image) {
            // Delete main image
            if (Storage::disk('public')->exists($partImage->image)) {
                Storage::disk('public')->delete($partImage->image);
            }

            // Delete thumbnail
            $thumbnailPath = 'thumbnails/' . basename($partImage->image);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
        }
    }
}