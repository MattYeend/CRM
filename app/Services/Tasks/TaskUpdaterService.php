<?php

namespace App\Services\Tasks;

use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;

class TaskUpdaterService
{
    /**
     * Update the task using request data.
     *
     * @param UpdateTaskRequest $request
     *
     * @param Task $task
     *
     * @return Task
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
