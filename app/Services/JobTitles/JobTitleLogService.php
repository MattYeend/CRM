<?php

namespace App\Services\JobTitles;

use App\Models\JobTitle;
use App\Models\Log;
use App\Models\User;

class JobTitleLogService
{
    /**
     * Log the creation of a JobTitle.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param JobTitle $jobTitle The lead that was created.
     *
     * @return Log The created log entry.
     */
    public function jobTitleCreated(
        User $user,
        int $userId,
        JobTitle $jobTitle
    ): array {
        $data = $this->baseJobTitleData($jobTitle) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_JOB_TITLE_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a JobTitle.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param JobTitle $jobTitle The jobTitle that was updated.
     *
     * @return Log The created log entry.
     */
    public function jobTitleUpdated(
        User $user,
        int $userId,
        JobTitle $jobTitle
    ): array {
        $data = $this->baseJobTitleData($jobTitle) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_JOB_TITLE_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a JobTitle.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param JobTitle $jobTitle The lead that was deleted.
     *
     * @return Log The created log entry.
     */
    public function jobTitleDeleted(
        User $user,
        int $userId,
        JobTitle $jobTitle
    ): array {
        $data = $this->baseJobTitleData($jobTitle) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_JOB_TITLE_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a JobTitle.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param JobTitle $jobTitle The jobTitle that was restored.
     *
     * @return Log The created log entry.
     */
    public function jobTitleRestored(
        User $user,
        int $userId,
        JobTitle $jobTitle
    ): array {
        $data = $this->baseJobTitleData($jobTitle) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_JOB_TITLE_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the common data array for a JobTitle log entry.
     *
     * @param JobTitle $jobTitle
     *
     * @return array
     */
    private function baseJobTitleData($jobTitle): array
    {
        return [
            'id' => $jobTitle->id,
            'title' => $jobTitle->title,
            'short_code' => $jobTitle->short_code,
            'group' => $jobTitle->group,
            'meta' => $jobTitle->meta,
        ];
    }
}
