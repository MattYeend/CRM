<?php

namespace App\Services;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;

class TaskCreatorService
{
    /**
     * Create a new task from request data.
     *
     * @param StoreTaskRequest $request
     *
     * @return Task
     */
    public function create(StoreTaskRequest $request): Task
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Task::create($data);
    }
}
