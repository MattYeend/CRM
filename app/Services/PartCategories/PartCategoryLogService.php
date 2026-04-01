<?php

namespace App\Services\PartCategories;

use App\Models\Log;
use App\Models\PartCategory;
use App\Models\User;

/**
 * Handles audit logging for PartCategory lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific part category action, combining base part category data with
 * action-specific timestamp and actor fields.
 */
class PartCategoryLogService
{
    /**
     * Log a partCategory creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartCategory $partCategory The part category that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function partCategoryCreated(
        User $user,
        int $userId,
        PartCategory $partCategory
    ): array {
        $data = $this->basePartCategoryData($partCategory) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_CATEGORY_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part category update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartCategory $partCategory The part category that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function partCategoryUpdated(
        User $user,
        int $userId,
        PartCategory $partCategory
    ): array {
        $data = $this->basePartCategoryData($partCategory) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_CATEGORY_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part category deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartCategory $partCategory The part category that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function partCategoryDeleted(
        User $user,
        int $userId,
        PartCategory $partCategory
    ): array {
        $data = $this->basePartCategoryData($partCategory) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_CATEGORY_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part category restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartCategory $partCategory The part category that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function partCategoryRestored(
        User $user,
        int $userId,
        PartCategory $partCategory
    ): array {
        $data = $this->basePartCategoryData($partCategory) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_CATEGORY_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all part category log entries.
     *
     * @param  PartCategory $partCategory The part category being logged.
     *
     * @return array The base fields extracted from the part category.
     */
    protected function basePartCategoryData(PartCategory $partCategory): array
    {
        return [
            'parent_id' => $partCategory->parent_id,
            'name' => $partCategory->name,
            'slug' => $partCategory->slug,
            'description' => $partCategory->description,
        ];
    }
}
