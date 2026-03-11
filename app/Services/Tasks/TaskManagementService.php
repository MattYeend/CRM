<?php

namespace App\Services\Tasks;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;

class TaskManagementService
{
    private TaskCreatorService $creator;
    private TaskUpdaterService $updater;
    private TaskDestructorService $destructor;

    public function __construct(
        TaskCreatorService $creator,
        TaskUpdaterService $updater,
        TaskDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new task.
     *
     * @param StoreTaskRequest $request
     *
     * @return Task
     */
    public function store(StoreTaskRequest $request): Task
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing task.
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
        return $this->updater->update($request, $task);
    }

    /**
     * Delete a task (soft delete).
     *
     * @param Task $task
     *
     * @return void
     */
    public function destroy(Task $task): void
    {
        $this->destructor->destroy($task);
    }

    /**
     * Restore a soft-deleted task
     *
     * @param int $id
     *
     * @return Task
     */
    public function restore(int $id): Task
    {
        return $this->destructor->restore($id);
    }
}
