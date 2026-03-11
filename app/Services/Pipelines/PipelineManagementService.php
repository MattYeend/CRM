<?php

namespace App\Services\Pipelines;

use App\Http\Requests\StorePipelineRequest;
use App\Http\Requests\UpdatePipelineRequest;
use App\Models\Pipeline;

class PipelineManagementService
{
    private PipelineCreatorService $creator;
    private PipelineUpdaterService $updater;
    private PipelineDestructorService $destructor;

    public function __construct(
        PipelineCreatorService $creator,
        PipelineUpdaterService $updater,
        PipelineDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new pipeline.
     *
     * @param StorePipelineRequest $request
     *
     * @return Pipeline
     */
    public function store(StorePipelineRequest $request): Pipeline
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing pipeline.
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
        return $this->updater->update($request, $pipeline);
    }

    /**
     * Delete a pipeline (soft delete).
     *
     * @param Pipeline $pipeline
     *
     * @return void
     */
    public function destroy(Pipeline $pipeline): void
    {
        $this->destructor->destroy($pipeline);
    }

    /**
     * Restore a soft-deleted pipeline
     *
     * @param int $id
     *
     * @return Pipeline
     */
    public function restore(int $id): Pipeline
    {
        return $this->destructor->restore($id);
    }
}
