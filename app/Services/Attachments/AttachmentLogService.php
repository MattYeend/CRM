<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use App\Models\Log;
use App\Models\User;

/**
 * Handles audit logging for Attachment lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific attachment action, combining base attachment data with
 * action-specific timestamp and actor fields.
 */
class AttachmentLogService
{
    /**
     * Log an attachment upload event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Attachment $attachment The attachment that was uploaded.
     *
     * @return array The structured data written to the log entry.
     */
    public function attachmentUploaded(
        User $user,
        int $userId,
        Attachment $attachment
    ): array {
        $data = $this->baseAttachmentData($attachment) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ATTACHMENT_UPLOADED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log an attachment deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Attachment $attachment The attachment that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function attachmentDeleted(
        User $user,
        int $userId,
        Attachment $attachment
    ): array {
        $data = $this->baseAttachmentData($attachment) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ATTACHMENT_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log an attachment restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Attachment $attachment The attachment that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function attachmentRestored(
        User $user,
        int $userId,
        Attachment $attachment
    ): array {
        $data = $this->baseAttachmentData($attachment) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ATTACHMENT_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log an attachment download event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Attachment $attachment The attachment that was downloaded.
     *
     * @return array The structured data written to the log entry.
     */
    public function attachmentDownloaded(
        User $user,
        int $userId,
        Attachment $attachment
    ): array {
        $data = $this->baseAttachmentData($attachment) + [
            'downloaded_at' => now(),
            'downloaded_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_ATTACHMENT_DOWNLOADED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all attachment log entries.
     *
     * @param  Attachment $attachment The attachment being logged.
     *
     * @return array The base fields extracted from the attachment.
     */
    protected function baseAttachmentData(Attachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'file_name' => $attachment->file_name,
            'disk' => $attachment->disk,
            'path' => $attachment->path,
            'attachment_type' => $attachment->attachment_type,
            'attachment_id' => $attachment->attachment_id,
            'uploaded_by' => $attachment->uploaded_by,
            'size' => $attachment->size,
            'mime' => $attachment->mime,
        ];
    }
}
