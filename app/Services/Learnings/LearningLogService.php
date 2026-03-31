<?php

namespace App\Services\Learnings;

use App\Models\Learning;
use App\Models\Log;
use App\Models\User;

/**
 * Handles audit logging for Learning lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific learning action, combining base learning data with
 * action-specific timestamp and actor fields.
 */
class LearningLogService
{
    /**
     * Log a learning creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Learning $learning The learning that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function learningCreated(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'created_at' => now(),
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
     * Log a learning update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Learning $learning The learning that was updated.
     *
     * @return array The structured data written to the log entry.
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
     * Log a learning deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Learning $learning The learning that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function learningDeleted(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'deleted_at' => now(),
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
     * Log a learning restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Learning $learning The learning that was restored.
     *
     * @return array The structured data written to the log entry.
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
     * Log a learning completion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Learning $learning The learning that was completed.
     *
     * @return array The structured data written to the log entry.
     */
    public function learningComplete(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'learning_complete' => now(),
            'learning_complete_by' => $user->name,
            'is_complete' => $learning->is_complete,
        ];

        Log::log(
            Log::ACTION_LEARNING_COMPLETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a learning incompletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Learning $learning The learning that was incomplete.
     *
     * @return array The structured data written to the log entry.
     */
    public function learningIncomplete(
        User $user,
        int $userId,
        Learning $learning
    ): array {
        $data = $this->baseLearningData($learning) + [
            'learning_incomplete' => now(),
            'learning_incomplete_by' => $user->name,
            'is_complete' => $learning->is_complete,
        ];

        Log::log(
            Log::ACTION_LEARNING_INCOMPLETE,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a learning viewed event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Learning $learning The learning that was viewed.
     *
     * @return array The structured data written to the log entry.
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
     * Build the base data array shared across all learning log entries.
     *
     * @param  Learning $learning The learning being logged.
     *
     * @return array The base fields extracted from the learning.
     */
    private function baseLearningData(Learning $learning): array
    {
        return [
            'id' => $learning->id,
            'title' => $learning->title,
            'description' => $learning->description,
            'date' => $learning->date,
            'meta' => $learning->meta,
        ];
    }
}
