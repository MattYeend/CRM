<?php

namespace App\Services\PipelineStages;

use App\Http\Requests\StorePipelineStageRequest;
use App\Models\PipelineStage;

/**
 * Handles the creation of new Pipeline Stage records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Pipeline Stage.
 */
class PipelineStageCreatorService
{
    /**
     * Create a new pipeline stage from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StorePipelineStageRequest $request Validated request
     * containing pipeline stage data.
     *
     * @return PipelineStage The newly created pipeline stage record.
     */
    public function create(StorePipelineStageRequest $request): PipelineStage
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return PipelineStage::create($data);
    }
}
