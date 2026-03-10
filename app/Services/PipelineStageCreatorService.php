<?php

namespace App\Services;

use App\Http\Requests\StorePipelineStageRequest;
use App\Models\PipelineStage;

class PipelineStageCreatorService
{
    /**
     * Create a new pipeline stage from request data.
     *
     * @param StorePipelineStageRequest $request
     *
     * @return PipelineStage
     */
    public function create(StorePipelineStageRequest $request): PipelineStage
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return PipelineStage::create($data);
    }
}
