<?php

namespace App\Services\Attachments;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;

class AttachmentCreatorService
{
    protected AttachmentAttacherService $attacher;
    protected AttachmentFileService $fileService;

    public function __construct(
        AttachmentAttacherService $attacher,
        AttachmentFileService $fileService
    ) {
        $this->attacher = $attacher;
        $this->fileService = $fileService;
    }

    /**
     * Create a new attachment from request data.
     *
     * @param StoreAttachmentRequest $request
     */
    public function create(StoreAttachmentRequest $request): Attachment
    {
        $user = $request->user();

        $file = $request->file('file');
        $path = $file->store('attachments');

        // store file
        $attachment = $this->fileService->storeFile(
            $file,
            $user->id
        );

        // attach to polymorphic model
        $validated = $request->validated();
        $this->attacher->attach(
            $validated['attachable_type'] ?? null,
            $validated['attachable_id'] ?? null,
            $attachment
        );

        $data = $request->validated();
        $data['filename'] = $file->getClientOriginalName();
        $data['uploaded_by'] = $user->id;
        $data['path'] = $path;
        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Attachment::create($data);
    }
}
