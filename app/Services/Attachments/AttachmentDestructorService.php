<?php

namespace App\Services\Attachments;

use App\Models\Attachment;

class AttachmentDestructorService
{
    /**
     * Soft-delete a attachment.
     *
     * @param Attachment $attachment
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
     * Restore a trashed attachment.
     *
     * @param int $id
     *
     * @return Attachment
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
