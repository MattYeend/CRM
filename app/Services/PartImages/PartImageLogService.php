<?php

namespace App\Services\PartImages;

use App\Models\Log;
use App\Models\PartImage;
use App\Models\User;

class PartImageLogService
{
    /**
     * Log the creation of a Part Image.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartImage $partImage The part was created.
     *
     * @return Log The created log entry.
     */
    public function partImageCreated(
        User $user,
        int $userId,
        PartImage $partImage
    ): array {
        $data = $this->basePartImageData($partImage) + [
            'created_at' => $partImage->created_at,
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
     * Log the update of a Part Image.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartImage $partImage The part was updated.
     *
     * @return Log The created log entry.
     */
    public function partImageUpdated(
        User $user,
        int $userId,
        PartImage $partImage
    ): array {
        $data = $this->basePartImageData($partImage) + [
            'updated_at' => $partImage->updated_at,
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
     * Log the deletion of a Part Image.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartImage $partImage The part was deleted.
     *
     * @return Log The created log entry.
     */
    public function partImageDeleted(
        User $user,
        int $userId,
        PartImage $partImage
    ): array {
        $data = $this->basePartImageData($partImage) + [
            'deleted_at' => $partImage->deleted_at,
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
     * Log the restoration of a Part Image.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartImage $partImage The part was restored.
     *
     * @return Log The created log entry.
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
     * Prepare the base data for logging a PartImage.
     *
     * @param PartImage $partImage The part image being logged.
     *
     * @return array The base data array.
     */
    protected function basePartImageData(PartImage $partImage): array
    {
        return array(
            'id' => $partImage->id,
            'part_id' => $partImage->part_id,
            'path' => $partImage->path,
            'alt' => $partImage->alt,
            'is_primary' => $partImage->is_primary,
            'sort_order' => $partImage->sort_order,
            'is_test' => $partImage->is_test,
            'meta' => $partImage->meta,
        );
    }
}