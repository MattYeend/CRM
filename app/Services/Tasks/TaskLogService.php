<?php

namespace App\Services\Tasks;

use App\Models\Log;
use App\Models\Task;
use App\Models\User;

/**
 * Handles audit logging for Task lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific task action, combining base task data with
 * action-specific timestamp and actor fields.
 */
class TaskLogService
{
    /**
     * Log a task creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Task $task The task that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function taskCreated(
        User $user,
        int $userId,
        Task $task
    ): array {
        $data = $this->baseTaskData($task) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_TASK_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a task update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Task $task The task that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function taskUpdated(
        User $user,
        int $userId,
        Task $task
    ): array {
        $data = $this->baseTaskData($task) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_TASK_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a task deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Task $task The task that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function taskDeleted(
        User $user,
        int $userId,
        Task $task
    ): array {
        $data = $this->baseTaskData($task) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_TASK_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a task restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Task $task The task that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function taskRestored(
        User $user,
        int $userId,
        Task $task
    ): array {
        $data = $this->baseTaskData($task) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_TASK_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a task completion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Task $task The task that was completed.
     *
     * @return array The structured data written to the log entry.
     */
    public function taskCompleted(
        User $user,
        int $userId,
        Task $task
    ): array {
        $data = $this->baseTaskData($task) + [
            'completed_at' => now(),
            'completed_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_TASK_COMPLETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a task reopening event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Task $task The task that was reopened.
     *
     * @return array The structured data written to the log entry.
     */
    public function taskReopened(
        User $user,
        int $userId,
        Task $task
    ): array {
        $data = $this->baseTaskData($task) + [
            'reopened_at' => now(),
            'reopened_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_TASK_REOPENED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all task log entries.
     *
     * @param  Task $task The task to extract data from.
     *
     * @return array The base task data.
     */
    protected function baseTaskData(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'assigned_to' => $task->assigned_to,
            'taskable_type' => $task->taskable_type,
            'taskable_id' => $task->taskable_id,
            'priority' => $task->priority,
            'status' => $task->status,
            'due_at' => $task->due_at,
        ];
    }
}
