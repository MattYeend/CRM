<?php

namespace App\Services\Tasks;

use App\Models\Task;

/**
 * Handles the soft-deletion and restoration of Task records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class TaskDestructorService
{
    /**
     * Soft-delete a task.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the task.
     *
     * @param Task $task The task instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Task $task): void
    {
        $userId = auth()->id();

        $task->update([
            'deleted_by' => $userId,
        ]);

        $task->delete();
    }

    /**
     * Restore a soft-deleted task.
     *
     * Looks up the task including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the task. Returns the task unchanged if it is not currently
     * trashed.
     *
     * @param int $id The primary key of the soft-deleted task.
     *
     * @return Task The restored task instance.
     */
    public function restore(int $id): Task
    {
        $userId = auth()->id();

        $task = Task::withTrashed()->findOrFail($id);

        if ($task->trashed()) {
            $task->update([
                'updated_by' => $userId,
            ]);
            $task->restore();
        }

        return $task;
    }
}
