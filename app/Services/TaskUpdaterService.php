<?php

namespace App\Services;

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
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $task->update($data);

        return $task->fresh();
    }
}
