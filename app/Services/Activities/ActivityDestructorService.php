<?php

namespace App\Services\Activities;

use App\Models\Activity;

class ActivityDestructorService
{
    /**
     * Soft-delete a activity.
     *
     * @param Activity $activity
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
     * @param int $id
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
