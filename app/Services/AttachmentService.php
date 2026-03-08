<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    /**
     * Store uploaded file and create Attachment model.
     *
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @param int|null $uploadedBy
     *
     * @return \App\Models\Attachment
     */
    public function storeFile(
        UploadedFile $file,
        ?int $uploadedBy = null
    ): Attachment {
        $path = $file->store('attachments', 'public');

        return Attachment::create([
            'filename' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getClientMimeType(),
            'uploaded_by' => $uploadedBy,
        ]);
    }

    /**
     * Replace file
     *
     * @param string $type
     *
     * @param int $id
     *
     * @return mixed|null
     */
    public function replaceFile(
        Attachment $attachment,
        UploadedFile $file,
        ?int $uploadedBy = null
    ): Attachment {
        if ($attachment->disk && $attachment->path) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $disk = config('filesystems.default');
        $path = $file->store('attachments', $disk);

        $attachment->update([
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $uploadedBy ?? $attachment->uploaded_by,
        ]);

        return $attachment;
    }

    /**
     * Delete the attachment file from disk (if present) and the model.
     *
     * @param \App\Models\Attachment $attachment
     *
     * @return void
     */
    public function delete(Attachment $attachment): void
    {
        if ($attachment->disk && $attachment->path) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $attachment->delete();
    }
}
