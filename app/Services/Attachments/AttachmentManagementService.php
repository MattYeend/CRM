<?php

namespace App\Services\Attachments;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Models\Attachment;

/**
 * Orchestrates attachment lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for attachment create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class AttachmentManagementService
{
    /**
     * Service responsible for creating new attachment records.
     *
     * @var AttachmentCreatorService
     */
    private AttachmentCreatorService $creator;

    /**
     * Service responsible for updating existing attachment records.
     *
     * @var AttachmentUpdaterService
     */
    private AttachmentUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring attachment records.
     *
     * @var AttachmentDestructorService
     */
    private AttachmentDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  AttachmentCreatorService $creator Handles attachment creation.
     * @param  AttachmentUpdaterService $updater Handles attachment updates.
     * @param  AttachmentDestructorService $destructor Handles attachment
     * deletion and restoration.
     */
    public function __construct(
        AttachmentCreatorService $creator,
        AttachmentUpdaterService $updater,
        AttachmentDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new attachment.
     *
     * @param  StoreAttachmentRequest $request Validated request containing
     * the file and attachable context.
     *
     * @return Attachment The newly created attachment.
     */
    public function store(StoreAttachmentRequest $request): Attachment
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing attachment.
     *
     * @param  UpdateAttachmentRequest $request Validated request containing
     * updated attachment data.
     * @param  Attachment $attachment The attachment instance to update.
     *
     * @return Attachment The updated attachment.
     */
    public function update(
        UpdateAttachmentRequest $request,
        Attachment $attachment
    ): Attachment {
        return $this->updater->update($request, $attachment);
    }

    /**
     * Soft-delete an attachment.
     *
     * @param  Attachment $attachment The attachment instance to delete.
     *
     * @return void
     */
    public function destroy(Attachment $attachment): void
    {
        $this->destructor->destroy($attachment);
    }

    /**
     * Restore a soft-deleted attachment.
     *
     * @param  int $id The primary key of the soft-deleted attachment.
     *
     * @return Attachment The restored attachment.
     */
    public function restore(int $id): Attachment
    {
        return $this->destructor->restore($id);
    }
}
