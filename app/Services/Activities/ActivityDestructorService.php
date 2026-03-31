<?php

namespace App\Services\Activities;

use App\Models\Activity;

/**
 * Handles deletion and restoration of Activity models.
 *
 * This service encapsulates logic for soft-deleting and restoring activities,
 * including tracking which user performed the action and when it occurred.
 * It ensures audit-related fields are populated alongside model state changes.
 */
class ActivityDestructorService
{
    /**
     * Soft-delete a activity.
     *
     * Records deletion metadata before performing the soft delete.
     *
     * @param  Activity  $activity  The activity model to delete
     *
     * @return void
     */
    public function destroy(Activity $activity): void
    {
        $userId = auth()->id();

        $activity->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $activity->delete();
    }

    /**
     * Restore a trashed activity.
     *
     * Restores the activity if it is currently soft-deleted and records
     * restoration metadata.
     *
     * @param  int  $id  The ID of the activity to restore
     *
     * @return Activity
     */
    public function restore(int $id): Activity
    {
        $userId = auth()->id();

        $activity = Activity::withTrashed()->findOrFail($id);

        if ($activity->trashed()) {
            $activity->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $activity->restore();
        }

        return $activity;
    }
}
