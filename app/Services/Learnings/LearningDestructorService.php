<?php

namespace App\Services\Learnings;

use App\Models\Learning;

/**
 * Handles soft deletion and restoration of Learning records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class LearningDestructorService
{
    /**
     * Soft-delete a learning.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the learning.
     *
     * @param  Learning $learning The learning instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Learning $learning): void
    {
        $userId = auth()->id();

        $learning->questions()->each(function ($question) {
            $question->answers()->delete();
            $question->delete();
        });

        $learning->update([
            'deleted_by' => $userId,
        ]);

        $learning->delete();
    }

    /**
     * Restore a soft-deleted learning.
     *
     * Looks up the learning including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the learning. Returns the learning unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted learning.
     *
     * @return Learning The restored learning instance.
     */
    public function restore(int $id): Learning
    {
        $userId = auth()->id();

        $learning = Learning::withTrashed()->findOrFail($id);

        if ($learning->trashed()) {
            $learning->update([
                'restored_by' => $userId,
            ]);
            $learning->restore();
        }

        return $learning;
    }
}
