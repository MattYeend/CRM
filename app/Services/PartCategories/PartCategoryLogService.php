<?php

namespace App\Services\PartCategories;

use App\Models\Log;
use App\Models\PartCategory;
use App\Models\User;

class PartCategoryLogService
{
    /**
     * Log the creation of a Part Category.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartCategory $partCategory The part was created.
     *
     * @return Log The created log entry.
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
     * Log the update of a Part Category.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartCategory $partCategory The part was updated.
     *
     * @return Log The created log entry.
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
     * Log the deletion of a Part Category.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartCategory $partCategory The part was deleted.
     *
     * @return Log The created log entry.
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
     * Log the restoration of a Part Category.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartCategory $partCategory The part was restored.
     *
     * @return Log The created log entry.
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
     * Prepare the base data for logging a Part Category.
     *
     * @param PartCategory $partCategory The part being logged.
     *
     * @return array The base data array.
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
