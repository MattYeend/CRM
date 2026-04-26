<?php

namespace App\Services\Activities;

use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;

/**
 * Handles updating of Activity models from validated request data.
 *
 * This service encapsulates the logic required to update existing
 * activities, including assigning audit metadata such as the user
 * performing the update and the timestamp of the change.
 */
class ActivityUpdaterService
{
    /**
     * Update the activity using request data.
     *
     * Populates audit fields before persisting changes to the model.
     *
     * @param  UpdateActivityRequest $request The validated request instance
     * @param  Activity $activity The activity model to update
     *
     * @return Activity
     */
    public function update(
        UpdateActivityRequest $request,
        Activity $activity
    ): Activity {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $activity->update($data);

        return $activity->fresh();
    }
}
