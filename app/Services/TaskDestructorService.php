<?php

namespace App\Services;

use App\Models\Task;

class TaskDestructorService
{
    /**
     * Soft-delete a task.
     *
     * @param Task $task
     *
     * @return void
     */
    public function destroy(Task $task): void
    {
        $task->update([
            'deleted_by' => auth()->id(),
        ]);

        $task->delete();
    }

    /**
     * Restore a trashed task.
     *
     * @param int $id
     *
     * @return Task
     */
    public function restore(int $id): Task
    {
        $task = Task::withTrashed()->findOrFail($id);

        if ($task->trashed()) {
            $task->restore();
        }

        return $task;
    }
}
