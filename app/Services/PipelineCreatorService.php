<?php

namespace App\Services;

use App\Http\Requests\StorePipelineRequest;
use App\Models\Pipeline;

class PipelineCreatorService
{
    /**
     * Create a new pipeline from request data.
     *
     * @param StorePipelineRequest $request
     *
     * @return Pipeline
     */
    public function create(StorePipelineRequest $request): Pipeline
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Pipeline::create($data);
    }
}
