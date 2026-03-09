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
     * @param UploadedFile $file
     *
     * @param int|null $uploadedBy
     *
     * @return Attachment
     */
    public function storeFile(
        UploadedFile $file,
        ?int $uploadedBy = null
    ): Attachment {
        $disk = config('filesystems.default');
        $path = $file->store('attachments', $disk);

        return Attachment::create([
            'filename' => $file->getClientOriginalName(),
            'disk' => $disk,
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
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
}
