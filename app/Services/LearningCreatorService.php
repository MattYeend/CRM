<?php

namespace App\Services;

use App\Http\Requests\StoreLearningRequest;
use App\Models\Learning;

class LearningCreatorService
{
    /**
     * Create a new learning from request data.
     *
     * @param StoreLearningRequest $request
     *
     * @return Learning
     */
    public function create(StoreLearningRequest $request): Learning
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Learning::create($data);
    }
}
