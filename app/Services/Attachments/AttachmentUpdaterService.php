<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use Illuminate\Http\Request;

class AttachmentUpdaterService
{
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

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $attachment->update($data);

        return $attachment->fresh();
    }
}
