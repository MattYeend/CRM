<?php

namespace App\Services\PartImages;

use App\Models\Log;
use App\Models\PartImage;
use App\Models\User;

/**
 * Handles audit logging for PartImage lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific part image action, combining base part image data with
 * action-specific timestamp and actor fields.
 */
class PartImageLogService
{
    /**
     * Log a part image creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartImage $partImage The part image that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function partImageCreated(
        User $user,
        int $userId,
        PartImage $partImage
    ): array {
        $data = $this->basePartImageData($partImage) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_IMAGE_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part image update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartImage $partImage The part image that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function partImageUpdated(
        User $user,
        int $userId,
        PartImage $partImage
    ): array {
        $data = $this->basePartImageData($partImage) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_IMAGE_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part image deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartImage $partImage The part image that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function partImageDeleted(
        User $user,
        int $userId,
        PartImage $partImage
    ): array {
        $data = $this->basePartImageData($partImage) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_IMAGE_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part image restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartImage $partImage The part image that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function partImageRestored(
        User $user,
        int $userId,
        PartImage $partImage
    ): array {
        $data = $this->basePartImageData($partImage) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_IMAGE_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all part image log entries.
     *
     * @param  PartImage $partImage The part image being logged.
     *
     * @return array The base fields extracted from the part image.
     */
    protected function basePartImageData(PartImage $partImage): array
    {
        return [
            'id' => $partImage->id,
            'part_id' => $partImage->part_id,
            'path' => $partImage->path,
            'alt' => $partImage->alt,
            'is_primary' => $partImage->is_primary,
            'sort_order' => $partImage->sort_order,
            'is_test' => $partImage->is_test,
            'meta' => $partImage->meta,
        ];
    }
}
