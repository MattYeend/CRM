<?php

namespace App\Services\Attachments;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;

class AttachmentCreatorService
{
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

        $data = $request->validated();
        $data['filename'] = $file->getClientOriginalName();
        $data['uploaded_by'] = $user->id;
        $data['path'] = $path;
        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Attachment::create($data);
    }
}
