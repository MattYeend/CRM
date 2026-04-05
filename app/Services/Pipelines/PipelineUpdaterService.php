<?php

namespace App\Services\Pipelines;

use App\Http\Requests\UpdatePipelineRequest;
use App\Models\Pipeline;

/**
 * Handles updates to Pipeline records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the pipeline.
 */
class PipelineUpdaterService
{
    /**
     * Update an existing pipeline.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the pipeline, and returns
     * a fresh instance.
     *
     * @param  UpdatePipelineRequest $request The request containing
     * validated pipeline data.
     * @param  Pipeline $pipeline The pipeline to update.
     *
     * @return Pipeline The updated pipeline instance.
     */
    public function update(
        UpdatePipelineRequest $request,
        Pipeline $pipeline
    ): Pipeline {
        $user = $request->user();
        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $pipeline->update($data);

        return $pipeline->fresh();
    }
}
