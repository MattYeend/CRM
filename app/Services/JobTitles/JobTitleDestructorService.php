<?php

namespace App\Services\JobTitles;

use App\Models\JobTitle;

class JobTitleDestructorService
{
    /**
     * Soft-delete a jobTitle.
     *
     * @param JobTitle $jobTitle
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
     * Restore a trashed jobTitle.
     *
     * @param int $id
     *
     * @return JobTitle
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
