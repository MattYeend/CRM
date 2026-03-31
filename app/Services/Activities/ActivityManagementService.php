<?php

namespace App\Services\Activities;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;

/**
 * Coordinates lifecycle operations for Activity models.
 *
 * This service acts as a facade over specialised activity services,
 * delegating creation, updating, deletion, and restoration logic to
 * dedicated classes. It provides a single entry point for managing
 * Activity models while keeping individual responsibilities separated.
 */
class ActivityManagementService
{
    private ActivityCreatorService $creator;
    private ActivityUpdaterService $updater;
    private ActivityDestructorService $destructor;

    /**
     * Create a new service instance.
     *
     * @param  ActivityCreatorService    $creator
     * @param  ActivityUpdaterService    $updater
     * @param  ActivityDestructorService $destructor
     *
     * @return void
     */
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
     * @param  StoreActivityRequest  $request  The validated request instance
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
     * @param  UpdateActivityRequest  $request   The validated request instance
     * @param  Activity               $activity  The activity model to update
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
     * Delegates to the destructor service to handle audit fields
     * and soft deletion.
     *
     * @param  Activity  $activity  The activity model to delete
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
     * @param  int  $id  The ID of the activity to restore
     *
     * @return Activity
     */
    public function restore(int $id): Activity
    {
        return $this->destructor->restore($id);
    }
}
