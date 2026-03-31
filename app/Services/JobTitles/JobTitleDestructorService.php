<?php

namespace App\Services\JobTitles;

use App\Models\JobTitle;

/**
 * Handles soft deletion and restoration of JobTitle records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class JobTitleDestructorService
{
    /**
     * Soft-delete a job title.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the job title.
     *
     * @param  JobTitle $jobTitle The job title instance to soft-delete.
     *
     * @return void
     */
    public function destroy(JobTitle $jobTitle): void
    {
        $userId = auth()->id();

        $jobTitle->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $jobTitle->delete();
    }

    /**
     * Restore a soft-deleted job title.
     *
     * Looks up the job title including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the job title. Returns the job title unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted job title.
     *
     * @return JobTitle The restored job title instance.
     */
    public function restore(int $id): JobTitle
    {
        $userId = auth()->id();

        $jobTitle = JobTitle::withTrashed()->findOrFail($id);

        if ($jobTitle->trashed()) {
            $jobTitle->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $jobTitle->restore();
        }

        return $jobTitle;
    }
}
