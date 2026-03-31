<?php

namespace App\Services\JobTitles;

use App\Http\Requests\StoreJobTitleRequest;
use App\Http\Requests\UpdateJobTitleRequest;
use App\Models\JobTitle;

/**
 * Orchestrates job title lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for job title create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class JobTitleManagementService
{
    /**
     * Service responsible for creating new job title records.
     *
     * @var JobTitleCreatorService
     */
    private JobTitleCreatorService $creator;

    /**
     * Service responsible for updating existing job title records.
     *
     * @var JobTitleUpdaterService
     */
    private JobTitleUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring job title records.
     *
     * @var JobTitleDestructorService
     */
    private JobTitleDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  JobTitleCreatorService $creator Handles job title creation.
     *
     * @param  JobTitleUpdaterService $updater Handles job title updates.
     *
     * @param  JobTitleDestructorService $destructor Handles job title deletion
     * and restoration.
     */
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
     * @param  StoreJobTitleRequest $request Validated request containing job
     * title data.
     *
     * @return JobTitle The newly created job title.
     */
    public function store(StoreJobTitleRequest $request): JobTitle
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing job title.
     *
     * @param  UpdateJobTitleRequest $request Validated request containing
     * updated job title data.
     *
     * @param  JobTitle $jobTitle The job title instance to update.
     *
     * @return JobTitle The updated job title.
     */
    public function update(
        UpdateJobTitleRequest $request,
        JobTitle $jobTitle
    ): JobTitle {
        return $this->updater->update($request, $jobTitle);
    }

    /**
     * Soft-delete a job title.
     *
     * @param  JobTitle $jobTitle The job title instance to delete.
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
     * @param  int $id The primary key of the soft-deleted job title.
     *
     * @return JobTitle The restored job title.
     */
    public function restore(int $id): JobTitle
    {
        return $this->destructor->restore($id);
    }
}
