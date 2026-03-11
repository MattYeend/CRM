<?php

namespace App\Services\Pipelines;

use App\Http\Requests\UpdatePipelineRequest;
use App\Models\Pipeline;

class PipelineUpdaterService
{
    /**
     * Update the pipeline using request data.
     *
     * @param UpdatePipelineRequest $request
     *
     * @param Pipeline $pipeline
     *
     * @return Pipeline
     */
    public function update(
        UpdatePipelineRequest $request,
        Pipeline $pipeline
    ): Pipeline {
        $user = $request->user();
        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $pipeline->update($data);

        return $pipeline->fresh();
    }
}
