<?php

namespace App\Services;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Models\Attachment;

class AttachmentManagementService
{
    private AttachmentCreatorService $creator;
    private AttachmentUpdaterService $updater;
    private AttachmentDestructorService $destructor;

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
     * @param StoreAttachmentRequest $request
     *
     * @return Attachment
     */
    public function store(StoreAttachmentRequest $request): Attachment
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing att$attachment.
     *
     * @param UpdateAttachmentRequest $request
     *
     * @param Attachment $attachment
     *
     * @return Attachment
     */
    public function update(
        UpdateAttachmentRequest $request,
        Attachment $attachment
    ): Attachment {
        return $this->updater->update($request, $attachment);
    }

    /**
     * Delete a atta$attachment (soft delete).
     *
     * @param Attachment $attachment
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
     * @param int $id
     *
     * @return Attachment
     */
    public function restore(int $id): Attachment
    {
        return $this->destructor->restore($id);
    }
}
