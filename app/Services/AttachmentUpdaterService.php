<?php

namespace App\Services;

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
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $attachment->update($data);

        return $attachment->fresh();
    }
}
