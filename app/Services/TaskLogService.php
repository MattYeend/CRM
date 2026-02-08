<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Task;
use App\Models\User;

class TaskLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Task.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Task $task The task was created.
     *
     * @return Log The created log entry.
     */
    public function taskCreated(
        User $user,
        int $userId,
        Task $task
    ): array {
        $data = $this->baseTaskData($task) + [
            'created_at' => $task->created_at,
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
     * Log the update of a Task.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Task $task The task was updated.
     *
     * @return Log The created log entry.
     */
    public function taskUpdated(
        User $user,
        int $userId,
        Task $task
    ): array {
        $data = $this->baseTaskData($task) + [
            'updated_at' => $task->updated_at,
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
     * Log the deletion of a Task.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Task $task The task was deleted.
     *
     * @return Log The created log entry.
     */
    public function taskDeleted(
        User $user,
        int $userId,
        Task $task
    ): array {
        $data = $this->baseTaskData($task) + [
            'deleted_at' => $task->deleted_at,
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
     * Log the restoration of a Task.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Task $task The task was restored.
     *
     * @return Log The created log entry.
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
     * Log the completion of a Task.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Task $task The task was completed.
     *
     * @return Log The created log entry.
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
     * Log the reopening of a Task.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Task $task The task was reopened.
     *
     * @return Log The created log entry.
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
     * Prepare base data for Task logging.
     *
     * @param Task $task The task to extract data from.
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
