<?php

namespace App\Services\JobTitles;

use App\Http\Requests\StoreJobTitleRequest;
use App\Models\JobTitle;

class JobTitleCreatorService
{
    /**
     * Create a new job title from request data.
     *
     * @param StoreJobTitleRequest $request
     *
     * @return JobTitle
     */
    public function create(StoreJobTitleRequest $request): JobTitle
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return JobTitle::create($data);
    }
}
