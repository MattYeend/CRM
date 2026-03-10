<?php

namespace App\Services;

use App\Http\Requests\UpdatePipelineStageRequest;
use App\Models\PipelineStage;

class PipelineStageUpdaterService
{
    /**
     * Update the pipeline stage using request data.
     *
     * @param UpdatePipelineStageRequest $request
     *
     * @param PipelineStage $pipelineStage
     *
     * @return PipelineStage
     */
    public function update(
        UpdatePipelineStageRequest $request,
        PipelineStage $pipelineStage
    ): PipelineStage {
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $pipelineStage->update($data);

        return $pipelineStage->fresh();
    }
}
