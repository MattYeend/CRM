<?php

namespace App\Services\Pipelines;

use App\Http\Requests\StorePipelineRequest;
use App\Models\Pipeline;

/**
 * Handles the creation of new Pipeline records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Pipeline.
 */
class PipelineCreatorService
{
    /**
     * Create a new pipeline from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StorePipelineRequest $request Validated request containing pipeline
     * data.
     *
     * @return Pipeline The newly created pipelin record.
     */
    public function create(StorePipelineRequest $request): Pipeline
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Pipeline::create($data);
    }
}
