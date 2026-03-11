<?php

namespace App\Services\Activities;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;

class ActivityManagementService
{
    private ActivityCreatorService $creator;
    private ActivityUpdaterService $updater;
    private ActivityDestructorService $destructor;

    public function __construct(
        ActivityCreatorService $creator,
        ActivityUpdaterService $updater,
        ActivityDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new activity.
     *
     * @param StoreActivityRequest $request
     *
     * @return Activity
     */
    public function store(StoreActivityRequest $request): Activity
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing activity.
     *
     * @param UpdateActivityRequest $request
     *
     * @param Activity $activity
     *
     * @return Activity
     */
    public function update(
        UpdateActivityRequest $request,
        Activity $activity
    ): Activity {
        return $this->updater->update($request, $activity);
    }

    /**
     * Delete a activity (soft delete).
     *
     * @param Activity $activity
     *
     * @return void
     */
    public function destroy(Activity $activity): void
    {
        $this->destructor->destroy($activity);
    }

    /**
     * Restore a soft-deleted activity.
     *
     * @param int $id
     *
     * @return Activity
     */
    public function restore(int $id): Activity
    {
        return $this->destructor->restore($id);
    }
}
