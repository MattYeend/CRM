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
        $activity->update([
            'deleted_by' => auth()->id(),
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
        $activity = Activity::withTrashed()->findOrFail($id);

        if ($activity->trashed()) {
            $activity->restore();
        }

        return $activity;
    }
}
