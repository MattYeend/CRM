<?php

namespace App\Services\Tasks;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;

/**
 * Handles the creation of new Task records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Task.
 */
class TaskCreatorService
{
    /**
     * Create a new task from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StoreTaskRequest $request Validated request containing task
     * data.
     *
     * @return Task The newly created task record.
     */
    public function create(StoreTaskRequest $request): Task
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Task::create($data);
    }
}
