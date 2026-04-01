<?php

namespace App\Services\Tasks;

use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;

/**
 * Handles updates to Task records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the task.
 */
class TaskUpdaterService
{
    /**
     * Update an existing task.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the task, and returns
     * a fresh instance.
     *
     * @param  UpdateTaskRequest $request The request containing
     * validated task data.
     * @param  Task $task The task to update.
     *
     * @return Task The updated task instance.
     */
    public function update(
        UpdateTaskRequest $request,
        Task $task
    ): Task {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $task->update($data);

        return $task->fresh();
    }
}
