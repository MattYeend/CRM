<?php

namespace App\Services;

use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;

class ActivityUpdaterService
{
    /**
     * Update the user using request data.
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
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $activity->update($data);

        return $activity->fresh();
    }
}
