<?php

namespace App\Services\Learnings;

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
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $learning->update($data);

        return $learning->fresh();
    }
}
