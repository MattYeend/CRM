<?php

namespace App\Services\Activities;

use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;

/**
 * Handles the creation of Activity models from validated request data.
 *
 * This service encapsulates the logic required to persist new activities,
 * including assigning ownership and setting default attributes derived
 * from the authenticated user context.
 */
class ActivityCreatorService
{
    /**
     * Create a new activity from request data.
     *
     * Populates ownership and timestamp fields before persisting
     * the Activity model.
     *
     * @param  StoreActivityRequest  $request  The validated request instance
     *
     * @return Activity
     */
    public function create(StoreActivityRequest $request): Activity
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Activity::create($data);
    }
}
