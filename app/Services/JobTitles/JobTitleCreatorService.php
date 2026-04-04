<?php

namespace App\Services\JobTitles;

use App\Http\Requests\StoreJobTitleRequest;
use App\Models\JobTitle;

/**
 * Handles the creation of new JobTitle records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new JobTitle.
 */
class JobTitleCreatorService
{
    /**
     * Create a new job title from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StoreJobTitleRequest $request Validated request containing job
     * title data.
     *
     * @return JobTitle The newly created job title record.
     */
    public function create(StoreJobTitleRequest $request): JobTitle
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return JobTitle::create($data);
    }
}
