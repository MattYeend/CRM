<?php

namespace App\Services\Attachments;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;

/**
 * Handles the creation of new Attachment records.
 *
 * Orchestrates file storage via AttachmentFileService, polymorphic model
 * association via AttachmentAttacherService, and final Attachment record
 * persistence from the validated request data.
 */
class AttachmentCreatorService
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
     * Inject the required services into the creator.
     *
     * @param  AttachmentAttacherService $attacher Handles polymorphic model
     * association.
     * @param  AttachmentFileService $fileService Handles file storage
     * operations.
     */
    public function __construct(
        AttachmentAttacherService $attacher,
        AttachmentFileService $fileService
    ) {
        $this->attacher = $attacher;
        $this->fileService = $fileService;
    }

    /**
     * Create a new attachment from the validated request data.
     *
     * Stores the uploaded file on disk, associates it with the polymorphic
     * parent model, and persists the Attachment record with audit fields
     * set from the authenticated user.
     *
     * @param  StoreAttachmentRequest $request Validated request containing
     * the file and attachable context.
     *
     * @return Attachment The newly created attachment record.
     */
    public function create(StoreAttachmentRequest $request): Attachment
    {
        $user = $request->user();
        $file = $request->file('file');
        $validated = $request->validated();

        $attachment = $this->fileService->storeFile($file, $user->id);

        $validated['created_by'] = $user->id;
        // Associate with the polymorphic parent if provided.
        $this->attacher->attach(
            $validated['attachable_type'] ?? null,
            isset($validated['attachable_id'])
                ? (int) $validated['attachable_id']
                : null,
            $attachment
        );

        return $attachment;
    }
}
