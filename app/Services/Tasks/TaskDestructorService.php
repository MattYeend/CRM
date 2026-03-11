<?php

namespace App\Services\Tasks;

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
        $userId = auth()->id();

        $task->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
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
        $userId = auth()->id();

        $task = Task::withTrashed()->findOrFail($id);

        if ($task->trashed()) {
            $task->update([
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
            $task->restore();
        }

        return $task;
    }
}
