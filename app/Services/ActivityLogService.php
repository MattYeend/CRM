<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Log;
use App\Models\User;

class ActivityLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log activity when a new activity is created.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Activity $activity The activity that was created.
     *
     * @return array The data logged for the activity.
     */
    public function activityCreated(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $activity->type,
            'scheduled_at' => $activity->scheduled_at,
            'subject_id' => $activity->subject_id,
            'description' => $activity->description,
            'created_at' => $activity->created_at,
        ];

        Log::log(
            Log::ACTION_ACTIVITY_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log activity when an activity is updated.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Activity $activity The activity that was updated.
     *
     * @return array The data logged for the activity.
     */
    public function activityUpdated(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $activity->type,
            'scheduled_at' => $activity->scheduled_at,
            'subject_id' => $activity->subject_id,
            'description' => $activity->description,
            'updated_at' => $activity->updated_at,
        ];

        Log::log(
            Log::ACTION_ACTIVITY_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log activity when an activity is deleted.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Activity $activity The activity that was deleted.
     *
     * @return array The data logged for the activity.
     */
    public function activityDeleted(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $activity->type,
            'scheduled_at' => $activity->scheduled_at,
            'subject_id' => $activity->subject_id,
            'description' => $activity->description,
            'deleted_at' => now(),
        ];

        Log::log(
            Log::ACTION_ACTIVITY_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log activity when an activity is completed.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Activity $activity The activity that was completed.
     *
     * @return array The data logged for the activity.
     */
    public function activityCompleted(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $activity->type,
            'scheduled_at' => $activity->scheduled_at,
            'subject_id' => $activity->subject_id,
            'description' => $activity->description,
            'completed_at' => now(),
        ];

        Log::log(
            Log::ACTION_ACTIVITY_COMPLETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log activity when an activity is reopened.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Activity $activity The activity that was reopened.
     *
     * @return array The data logged for the activity.
     */
    public function activityReopened(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $activity->type,
            'scheduled_at' => $activity->scheduled_at,
            'subject_id' => $activity->subject_id,
            'description' => $activity->description,
            'reopened_at' => now(),
        ];

        Log::log(
            Log::ACTION_ACTIVITY_REOPENED,
            $data,
            $userId,
        );

        return $data;
    }
}
