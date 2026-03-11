<?php

namespace App\Services\Activities;

use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;

class ActivityUpdaterService
{
    /**
     * Update the activity using request data.
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
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $activity->update($data);

        return $activity->fresh();
    }
}
