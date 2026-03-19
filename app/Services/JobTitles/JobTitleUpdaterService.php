<?php

namespace App\Services\JobTitles;

use App\Http\Requests\UpdateJobTitleRequest;
use App\Models\JobTitle;

class JobTitleUpdaterService
{
    /**
     * Update the job title using request data.
     *
     * @param UpdateJobTitleRequest $request
     *
     * @param JobTitle $jobTitle
     *
     * @return JobTitle
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
