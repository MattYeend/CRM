<?php

namespace App\Services\PipelineStages;

use App\Http\Requests\UpdatePipelineStageRequest;
use App\Models\PipelineStage;

/**
 * Handles updates to PipelineStage records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the pipeline stage.
 */
class PipelineStageUpdaterService
{
    /**
     * Update an existing pipeline stage.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the pipeline stage, and returns
     * a fresh instance.
     *
     * @param  UpdatePipelineStageRequest $request The request containing
     * validated pipeline stage data.
     * @param  PipelineStage $pipelineStage The pipeline stage to update.
     *
     * @return PipelineStage The updated pipeline stage instance.
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
