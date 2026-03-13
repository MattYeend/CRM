<?php

namespace App\Services\JobTitles;

use App\Http\Requests\StoreJobTitleRequest;
use App\Http\Requests\UpdateJobTitleRequest;
use App\Models\JobTitle;

class JobTitleManagementService
{
    private JobTitleCreatorService $creator;
    private JobTitleUpdaterService $updater;
    private JobTitleDestructorService $destructor;

    public function __construct(
        JobTitleCreatorService $creator,
        JobTitleUpdaterService $updater,
        JobTitleDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new job title.
     *
     * @param StoreJobTitleRequest $request
     *
     * @return JobTitle
     */
    public function store(StoreJobTitleRequest $request): JobTitle
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing jobTitle.
     *
     * @param UpdateJobTitleRequest $request
     *
     * @param JobTitle $jobTitle
     *
     * @return JobTitle
     */
    public function update(
        UpdateJobTitleRequest $request,
        JobTitle $jobTitle
    ): JobTitle {
        return $this->updater->update($request, $jobTitle);
    }

    /**
     * Delete a jobTitle (soft delete).
     *
     * @param JobTitle $jobTitle
     *
     * @return void
     */
    public function destroy(JobTitle $jobTitle): void
    {
        $this->destructor->destroy($jobTitle);
    }

    /**
     * Restore a soft-deleted job title.
     *
     * @param int $id
     *
     * @return JobTitle
     */
    public function restore(int $id): JobTitle
    {
        return $this->destructor->restore($id);
    }
}
