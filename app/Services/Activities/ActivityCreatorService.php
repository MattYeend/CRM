<?php

namespace App\Services\Activities;

use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;

class ActivityCreatorService
{
    /**
     * Create a new activity from request data.
     *
     * @param StoreActivityRequest $request
     *
     * @return Activity
     */
    public function create(StoreActivityRequest $request): Activity
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Activity::create($data);
    }
}
