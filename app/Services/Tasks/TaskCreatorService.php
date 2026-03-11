<?php

namespace App\Services\Tasks;

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
        $data['created_at'] = now();

        return Task::create($data);
    }
}
