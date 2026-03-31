<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Models\Log;
use App\Models\User;

/**
 * Handles logging of Activity model events.
 *
 * This service centralises the creation of structured log entries for
 * Activity models, including lifecycle events such as creation, updates,
 * deletion, restoration, completion, and reopening.
 *
 * Each log entry includes base activity data along with contextual metadata
 * about the user who performed the action and the timestamp at which it
 * occurred.
 */
class ActivityLogService
{
    /**
     * Log activity when a new activity is created.
     *
     * @param  User      $user      The user being logged
     * @param  int       $userId    The ID of the user who performed the action
     * @param  Activity  $activity  The activity that was created
     *
     * @return array
     */
    public function activityCreated(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = $this->baseActivityData($activity) + [
            'created_by' => $user->name,
            'created_at' => now(),
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
     * @param  User      $user      The user being logged
     * @param  int       $userId    The ID of the user who performed the action
     * @param  Activity  $activity  The activity that was updated
     *
     * @return array
     */
    public function activityUpdated(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = $this->baseActivityData($activity) + [
            'updated_by' => $user->name,
            'updated_at' => now(),
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
     * @param  User      $user      The user being logged
     * @param  int       $userId    The ID of the user who performed the action
     * @param  Activity  $activity  The activity that was deleted
     *
     * @return array
     */
    public function activityDeleted(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = $this->baseActivityData($activity) + [
            'deleted_by' => $user->name,
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
     * Log activity when an activity is restored.
     *
     * @param  User      $user      The user being logged
     * @param  int       $userId    The ID of the user who performed the action
     * @param  Activity  $activity  The activity that was restored
     *
     * @return array
     */
    public function activityRestored(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = $this->baseActivityData($activity) + [
            'restored_by' => $user->name,
            'restored_at' => now(),
        ];

        Log::log(
            Log::ACTION_ACTIVITY_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log activity when an activity is completed.
     *
     * @param  User      $user      The user being logged
     * @param  int       $userId    The ID of the user who performed the action
     * @param  Activity  $activity  The activity that was completed
     *
     * @return array
     */
    public function activityCompleted(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = $this->baseActivityData($activity) + [
            'completed_by' => $user->name,
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
     * @param  User      $user      The user being logged
     * @param  int       $userId    The ID of the user who performed the action
     * @param  Activity  $activity  The activity that was reopened
     *
     * @return array
     */
    public function activityReopened(
        User $user,
        int $userId,
        Activity $activity
    ): array {
        $data = $this->baseActivityData($activity) + [
            'restored_by' => $user->name,
            'restored_at' => now(),
        ];

        Log::log(
            Log::ACTION_ACTIVITY_REOPENED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Construct the base data array for an Activity model.
     *
     * Provides a consistent subset of activity attributes included
     * in all log entries.
     *
     * @param  Activity  $activity  The activity being logged
     *
     * @return array
     */
    protected function baseActivityData(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'type' => $activity->type,
            'scheduled_at' => $activity->scheduled_at,
            'subject_id' => $activity->subject_id,
            'description' => $activity->description,
        ];
    }
}
