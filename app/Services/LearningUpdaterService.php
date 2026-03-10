<?php

namespace App\Services;

use App\Http\Requests\UpdateLearningRequest;
use App\Models\Learning;

class LearningUpdaterService
{
    /**
     * Update the learning using request data.
     *
     * @param UpdateLearningRequest $request
     *
     * @param Learning $learning
     *
     * @return Learning
     */
    public function update(
        UpdateLearningRequest $request,
        Learning $learning
    ): Learning {
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $learning->update($data);

        return $learning->fresh();
    }
}
