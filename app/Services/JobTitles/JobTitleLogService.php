<?php

namespace App\Services\JobTitles;

use App\Models\JobTitle;
use App\Models\Log;
use App\Models\User;

/**
 * Handles audit logging for JobTitle lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific job title action, combining base job title data with
 * action-specific timestamp and actor fields.
 */
class JobTitleLogService
{
    /**
     * Log a job title creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  JobTitle $jobTitle The job title that was created.
     *
     * @return array The structured data written to the log entry.
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
     * Log a job title update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  JobTitle $jobTitle The job title that was updated.
     *
     * @return array The structured data written to the log entry.
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
     * Log a job title deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  JobTitle $jobTitle The job title that was deleted.
     *
     * @return array The structured data written to the log entry.
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
     * Log a job title restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  JobTitle $jobTitle The job title that was restored.
     *
     * @return array The structured data written to the log entry.
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
     * Build the base data array shared across all job title log entries.
     *
     * @param  JobTitle $jobTitle The job title being logged.
     *
     * @return array The base fields extracted from the job title.
     */
    private function baseJobTitleData(JobTitle $jobTitle): array
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
