<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Handles file storage and management for Attachment models.
 *
 * This service is responsible for persisting uploaded files to storage,
 * creating corresponding Attachment models, and replacing existing files
 * while ensuring old files are properly removed.
 */
class AttachmentFileService
{
    /**
     * Store an uploaded file and create an Attachment model.
     *
     * Persists the file to the configured filesystem disk and records
     * its metadata in a new Attachment model.
     *
     * @param  UploadedFile $file The uploaded file instance
     * @param  int|null $uploadedBy The ID of the user uploading the file
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
            'mime' => $file->getMimeType(),
            'uploaded_by' => $uploadedBy,
        ]);
    }

    /**
     * Replace the file associated with an Attachment model.
     *
     * Deletes the existing file from storage (if present), stores the new file,
     * and updates the attachment metadata accordingly.
     *
     * @param  Attachment $attachment The attachment to update
     * @param  UploadedFile $file The new uploaded file
     * @param  int|null $uploadedBy The ID of the user performing
     * the update
     *
     * @return Attachment
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
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $uploadedBy ?? $attachment->uploaded_by,
        ]);

        return $attachment;
    }

    /**
     * Get the authenticated download URL for an attachment.
     *
     * @param  Attachment $attachment
     *
     * @return string
     */
    public function getUrl(Attachment $attachment): string
    {
        if ($attachment->disk === 'local') {
            return route('attachments.download', $attachment);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($attachment->disk);

        return $disk->url($attachment->path);
    }
}
