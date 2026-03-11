<?php

namespace App\Services\PipelineStages;

use App\Http\Requests\StorePipelineStageRequest;
use App\Http\Requests\UpdatePipelineStageRequest;
use App\Models\PipelineStage;

class PipelineStageManagementService
{
    private PipelineStageCreatorService $creator;
    private PipelineStageUpdaterService $updater;
    private PipelineStageDestructorService $destructor;

    public function __construct(
        PipelineStageCreatorService $creator,
        PipelineStageUpdaterService $updater,
        PipelineStageDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new pipeline stage.
     *
     * @param StorePipelineStageRequest $request
     *
     * @return PipelineStage
     */
    public function store(StorePipelineStageRequest $request): PipelineStage
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing pipeline stage.
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
        return $this->updater->update($request, $pipelineStage);
    }

    /**
     * Delete a pipeline stage (soft delete).
     *
     * @param PipelineStage $pipelineStage
     *
     * @return void
     */
    public function destroy(PipelineStage $pipelineStage): void
    {
        $this->destructor->destroy($pipelineStage);
    }

    /**
     * Restore a soft-deleted pipeline stage
     *
     * @param int $id
     *
     * @return PipelineStage
     */
    public function restore(int $id): PipelineStage
    {
        return $this->destructor->restore($id);
    }
}
