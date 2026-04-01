<?php

namespace App\Services\Tasks;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;

/**
 * Orchestrates task lifecycle opperations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for task create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class TaskManagementService
{
    /**
     * Service responsible for creating new task records.
     *
     * @var TaskCreatorService
     */
    private TaskCreatorService $creator;

    /**
     * Service responsible for updating existing task records.
     *
     * @var TaskUpdaterService
     */
    private TaskUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring task records.
     *
     * @var TaskDestructorService
     */
    private TaskDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  TaskCreatorService $creator Handles task creation.
     * @param  TaskUpdaterService $updater Handles task updates.
     * @param  TaskDestructorService $destructor Handles task deletion
     * and restoration.
     */
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
     * @param  StoreTaskRequest $request Validated request containing task data.
     *
     * @return Task The newly created task.
     */
    public function store(StoreTaskRequest $request): Task
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing task.
     *
     * @param  UpdateTaskRequest $request Validated request containing updated
     * task data.
     * @param  Task $task The task record to update.
     *
     * @return Task The updated task.
     */
    public function update(
        UpdateTaskRequest $request,
        Task $task
    ): Task {
        return $this->updater->update($request, $task);
    }

    /**
     * Soft-delete a task.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Task $task The task to delete.
     *
     * @return void
     */
    public function destroy(Task $task): void
    {
        $this->destructor->destroy($task);
    }

    /**
     * Restore a soft-deleted task.
     *
     * @param  int $id The primary key of the soft-deleted task.
     *
     * @return Task The restored task.
     */
    public function restore(int $id): Task
    {
        return $this->destructor->restore($id);
    }
}
