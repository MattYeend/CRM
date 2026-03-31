<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use Illuminate\Http\Request;

/**
 * Handles updates to existing Attachment records.
 *
 * Optionally replaces the stored file via AttachmentFileService when a new
 * file is present in the request, updates the polymorphic association via
 * AttachmentAttacherService, and persists the updated attribute data with
 * audit fields.
 */
class AttachmentUpdaterService
{
    /**
     * Service responsible for attaching the file to its polymorphic parent.
     *
     * @var AttachmentAttacherService
     */
    protected AttachmentAttacherService $attacher;

    /**
     * Service responsible for storing and replacing attachment files on disk.
     *
     * @var AttachmentFileService
     */
    protected AttachmentFileService $fileService;

    /**
     * Inject the required services into the updater.
     *
     * @param  AttachmentAttacherService $attacher Handles polymorphic model
     * association.
     * @param  AttachmentFileService $fileService Handles file storage and
     * replacement operations.
     */
    public function __construct(
        AttachmentAttacherService $attacher,
        AttachmentFileService $fileService
    ) {
        $this->attacher = $attacher;
        $this->fileService = $fileService;
    }

    /**
     * Update the attachment using the validated request data.
     *
     * Replaces the stored file if a new file is provided, updates the
     * polymorphic association, and persists the remaining validated fields
     * with updated audit timestamps.
     *
     * @param  Request $request Incoming HTTP request containing optional file
     * and updated attachment data.
     * @param  Attachment $attachment The attachment instance to update.
     *
     * @return Attachment The updated and freshly reloaded attachment instance.
     */
    public function update(Request $request, Attachment $attachment): Attachment
    {
        $user = $request->user();

        $file = $request->file('file');

        if ($file) {
            $attachment = $this->fileService->replaceFile(
                $attachment,
                $file,
                $user->id
            );
        }

        $this->attacher->attach(
            $request->input('attachable_type'),
            $request->input('attachable_id'),
            $attachment
        );

        $data = $request->validated();
        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $attachment->update($data);

        return $attachment->fresh();
    }
}
