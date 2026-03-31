<?php

namespace App\Services\Attachments;

use App\Models\Attachment;

/**
 * Handles soft deletion and restoration of Attachment records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class AttachmentDestructorService
{
    /**
     * Soft-delete an attachment.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the attachment.
     *
     * @param  Attachment $attachment The attachment instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Attachment $attachment): void
    {
        $userId = auth()->id();

        $attachment->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $attachment->delete();
    }

    /**
     * Restore a soft-deleted attachment.
     *
     * Looks up the attachment including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the attachment. Returns the attachment unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted attachment.
     *
     * @return Attachment The restored attachment instance.
     */
    public function restore(int $id): Attachment
    {
        $userId = auth()->id();

        $attachment = Attachment::withTrashed()->findOrFail($id);

        if ($attachment->trashed()) {
            $attachment->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $attachment->restore();
        }

        return $attachment;
    }
}
