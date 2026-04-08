<?php

namespace App\Services\Industries;

use App\Models\Industry;
use App\Models\Log;
use App\Models\User;

/**
 * Handles logging of Industry-related actions.
 *
 * Provides methods to log creation, update, deletion, and restoration
 * of industry records, recording the responsible user and timestamps.
 */
class IndustryLogService
{
    /**
     * Log the creation of a industry.
     *
     * Records the user who created the industry and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Industry $industry The industry being created.
     *
     * @return array The logged data for the creation action.
     */
    public function industryCreated(
        User $user,
        int $userId,
        Industry $industry
    ): array {
        $data = $this->baseIndustryData($industry) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_COMPANY_INDUSTRY_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a industry.
     *
     * Records the user who updated the industry and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Industry $industry The industry being updated.
     *
     * @return array The logged data for the update action.
     */
    public function industryUpdated(
        User $user,
        int $userId,
        Industry $industry
    ): array {
        $data = $this->baseIndustryData($industry) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_COMPANY_INDUSTRY_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a industry.
     *
     * Records the user who deleted the industry and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Industry $industry The industry being deleted.
     *
     * @return array The logged data for the deletion action.
     */
    public function industryDeleted(
        User $user,
        int $userId,
        Industry $industry
    ): array {
        $data = $this->baseIndustryData($industry) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_COMPANY_INDUSTRY_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a industry.
     *
     * Records the user who restored the company and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Industry $industry The company being restored.
     *
     * @return array The logged data for the restoration action.
     */
    public function industryRestored(
        User $user,
        int $userId,
        Industry $industry
    ): array {
        $data = $this->baseIndustryData($industry) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_COMPANY_INDUSTRY_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare the base data for logging a industry.
     *
     * Extracts relevant attributes from the industry for logging purposes.
     *
     * @param  Industry $industry The industry to extract data from.
     *
     * @return array The base data array to be included in logs.
     */
    protected function baseIndustryData(Industry $industry): array
    {
        return [
            'id' => $industry->id,
            'name' => $industry->name,
            'slug' => $industry->slug,
        ];
    }
}
