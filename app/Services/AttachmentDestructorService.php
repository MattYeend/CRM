<?php

namespace App\Services;

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
        $attachment->update([
            'deleted_by' => auth()->id(),
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
        $attachment = Attachment::withTrashed()->findOrFail($id);

        if ($attachment->trashed()) {
            $attachment->restore();
        }

        return $attachment;
    }
}
