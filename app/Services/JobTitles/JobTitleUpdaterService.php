<?php

namespace App\Services\JobTitles;

use App\Http\Requests\UpdateJobTitleRequest;
use App\Models\JobTitle;

/**
 * Handles updates to JobTitle records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the job title.
 */
class JobTitleUpdaterService
{
    /**
     * Update an existing job title.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the job title, and returns
     * a fresh instance.
     *
     * @param  UpdateJobTitleRequest $request The request containing
     * validated job title data.
     * @param  JobTitle $jobTitle The job title to update.
     *
     * @return JobTitle The updated job title instance.
     */
    public function update(
        UpdateJobTitleRequest $request,
        JobTitle $jobTitle
    ): JobTitle {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $jobTitle->update($data);

        return $jobTitle->fresh();
    }
}
