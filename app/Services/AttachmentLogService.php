<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Log;
use App\Models\User;

class AttachmentLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the attachment of a file.
     *
     * @param User $user The user that was created.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Attachment $attachment The attachment being logged.
     *
     * @return Log The created log entry.
     */
    public function attachmentUploaded(
        User $user,
        int $userId,
        Attachment $attachment
    ): array {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'file_name' => $attachment->file_name,
            'disk' => $attachment->disk,
            'path' => $attachment->path,
            'attachment_type' => $attachment->attachment_type,
            'attachment_id' => $attachment->attachment_id,
            'uploaded_by' => $attachment->uploaded_by,
            'size' => $attachment->size,
            'mime' => $attachment->mime,
            'created_at' => $attachment->created_at,
        ];

        Log::log(
            Log::ACTION_ATTACHMENT_UPLOADED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the attachment of a file being deleted.
     *
     * @param User $user The user that was created.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Attachment $attachment The attachment being logged.
     *
     * @return Log The created log entry.
     */
    public function attachmentDeleted(
        User $user,
        int $userId,
        Attachment $attachment
    ): array {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'file_name' => $attachment->file_name,
            'disk' => $attachment->disk,
            'path' => $attachment->path,
            'attachment_type' => $attachment->attachment_type,
            'attachment_id' => $attachment->attachment_id,
            'uploaded_by' => $attachment->uploaded_by,
            'size' => $attachment->size,
            'mime' => $attachment->mime,
            'deleted_at' => now(),
        ];

        Log::log(
            Log::ACTION_ATTACHMENT_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the attachment of a file being downloaded.
     *
     * @param User $user The user that was created.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Attachment $attachment The attachment being logged.
     *
     * @return Log The created log entry.
     */
    public function attachmentDownloaded(
        User $user,
        int $userId,
        Attachment $attachment
    ): array {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'file_name' => $attachment->file_name,
            'disk' => $attachment->disk,
            'path' => $attachment->path,
            'attachment_type' => $attachment->attachment_type,
            'attachment_id' => $attachment->attachment_id,
            'uploaded_by' => $attachment->uploaded_by,
            'size' => $attachment->size,
            'mime' => $attachment->mime,
            'downloaded_at' => now(),
        ];

        Log::log(
            Log::ACTION_ATTACHMENT_DOWNLOADED,
            $data,
            $userId,
        );

        return $data;
    }
}
