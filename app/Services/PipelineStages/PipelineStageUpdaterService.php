<?php

namespace App\Services\PipelineStages;

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
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $pipelineStage->update($data);

        return $pipelineStage->fresh();
    }
}
