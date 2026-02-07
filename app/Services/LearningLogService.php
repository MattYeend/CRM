<?php

namespace App\Services;

use App\Models\Learning;
use App\Models\Log;
use App\Models\User;

class LearningLogService
{
    public function __construct()
    {
        // Empty Constructor
    }

    /**
     * Log the creation of an Learning.
     *
     * @param User $user The user that was created.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Learning $learning The learning being logged.
     *
     * @return Log The created log entry.
     */
    public function learningCreated(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'created_at' => $learning->created_at,
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEARNING_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of an Learning.
     *
     * @param User $user The user that was updated.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Learning $learning The learning being logged.
     *
     * @return Log The created log entry.
     */
    public function learningUpdated(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEARNING_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of an Learning.
     *
     * @param User $user The user that was doing the action.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Learning $learning The learning being deleted.
     *
     * @return Log The created log entry.
     */
    public function learningDeleted(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'deleted_at' => $learning->deleted_at,
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEARNING_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of an Learning.
     *
     * @param User $user The user that was restored.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Learning $learning The learning being logged.
     *
     * @return Log The created log entry.
     */
    public function learningRestored(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEARNING_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the complete status of an Learning.
     *
     * @param User $user The user that marked the learning as overdue.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Learning $learning The learning being logged.
     *
     * @return Log The created log entry.
     */
    public function learningComplete(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'learning_complete' => now(),
            'learning_complete_by' => $user->name,
            'is_completed' => $learning->is_completed,
        ];

        Log::log(
            Log::ACTION_LEARNING_COMPLETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the incomplete status of an Learning.
     *
     * @param User $user The user that marked the learning as incomplete.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Learning $learning The learning being logged.
     */
    public function learningIncomplete(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'learning_incomplete' => now(),
            'learning_incomplete_by' => $user->name,
            'is_completed' => $learning->is_completed,
        ];

        Log::log(
            Log::ACTION_LEARNING_INCOMPLETE,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the viewing of an Learning.
     *
     * @param User $user The user that viewed the learning.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Learning $learning The learning being logged.
     */
    public function learningViewed(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'viewed_at' => now(),
            'viewed_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEARNING_VIEWED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the common data array for an Learning log entry.
     *
     * @param Learning $learning
     *
     * @return array
     */
    private function baseLearningData(Learning $learning): array
    {
        return [
            'id' => $learning->id,
            'title' => $learning->title,
            'description' => $learning->description,
            'date' => $learning->date,
        ];
    }
}
