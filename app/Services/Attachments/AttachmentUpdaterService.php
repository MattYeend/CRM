<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use Illuminate\Http\Request;

class AttachmentUpdaterService
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
     * Update the attachment using request data.
     *
     * @param Request $request
     *
     * @param Attachment $attachment
     *
     * @return Attachment
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
